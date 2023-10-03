import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';

import { Asset } from 'src/app/shared/models/asset.model';
import { Auction } from 'src/app/shared/models/auction.model';

import { formErrors, lte, dateCoherence } from 'src/app/shared/validators';
import { Maximo } from 'src/app/shared/models/data.model';

@Component({
	selector: 'app-direct-selling-create',
	templateUrl: './direct-selling-create.component.html',
	styleUrls: ['./direct-selling-create.component.scss']
})
export class DirectSellingCreateComponent implements OnInit {

	editHtml1 = false;
	editHtml2 = false;
	editHtml3 = false;
	editHtml4 = false;

	urlUsed = false;
	// Form

	public directSelling: Auction = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }

	public editor = ClassicEditor;
	public ckOptions = null;

	// Selectors

	public assets: Asset[] = [];
  public maximoId: any;

	// Modals

	@ViewChild('assetModal') assetModal: TemplateRef<any>;
	public modalRef: BsModalRef;

	private MAX_FILE_SIZE: number = 8000000;

	constructor(
		private route: ActivatedRoute,
		private router: Router,
		private modalService: BsModalService,
		public fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService,
		public auctionsService: AuctionsService
	) {
	}

	ngOnInit(): void {

		this.ckOptions = this.utils.ckOptions;

		this.getAssets();
    this.getMaxId();
		this.setFormFields();
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {
	}

	getAssets() {

		this.dataService.getAuctionAssets()
		.then((val: Asset) => {
			this.assets = val['response'];
			// N.B.: this is because when retrieving selector data again
			// the selector strangely autoselects itself without updating the form
			this.assets.unshift({ id: null, name: '' } as Asset);
		});
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			title: ['', [Validators.required]],
			active_id: ['', [Validators.required]],
			start_date: ['', [Validators.required]],
			start_time: ['', [Validators.required]],
			end_date: ['', [Validators.required]],
			end_time: ['', [Validators.required]],
			start_price: ['', [Validators.required]],
			minimum_bid: [''],
			appraisal_value: ['', [Validators.required]],
			commission: ['', [Validators.required, lte(100)]],
			deposit: [''],
			featured: [''],
			dontshowtimer: [''],
			video: [''],
			video_file: [''],
			idealista: [''],
			rrss: [''],
			repercusion: [''],
			mailing: [''],
			auto: [''],
			juzgado: [''],
			description: [''],
			description_document: [''],
			description_document_two: [''],
			technical_specifications: [''],
			technical_document: [''],
			technical_document_two: [''],
			land_registry: [''],
			land_registry_document: [''],
			land_registry_document_two: [''],
			conditions: [''],
			conditions_document: [''],
			conditions_document_two: [''],
			link_rewrite: ['', [Validators.required]],
			meta_description: [''],
			meta_title: [''],
			meta_keywords: [''],
		});

		this.form.setValidators(dateCoherence());

		this.route.queryParams
			.subscribe(params => {
				if (params.hasOwnProperty('active_id')) {
					this.form.get('active_id').setValue(params.active_id);
				}
			});
	}

	uploadFile(event: any, key: string) {

		if (event.target.files && event.target.files[0]) {

			const file = event.target.files[0];
			const reader = new FileReader();

			if (file.size > this.MAX_FILE_SIZE) {
				this.utils.showToast(`Un archivo supera los 8MB  ${file.name})`, 'error');
				return;
			}

			reader.onload = (evt: any) => {
				this.form.get(key).setValue(file);
			};
			reader.readAsDataURL(file);
		}
	}

	saveDirectSelling(draft = false) {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.directSelling);

			const data = new FormData();

			let startDate = this.form.get('start_date').value;
			let startTime = this.form.get('start_time').value + ':00';

			let endDate = this.form.get('end_date').value;
			let endTime = this.form.get('end_time').value + ':00';

			data.append('auction_status_id', draft ? '2' : '1');
			data.append('title', this.form.get('title').value);
			data.append('active_id', this.form.get('active_id').value);
			data.append('start_date', `${startDate} ${startTime}`);
			data.append('end_date', `${endDate} ${endTime}`);
			data.append('start_price', this.form.get('start_price').value);
			data.append('deposit', this.form.get('deposit').value ?? '0');
			data.append('minimum_bid', this.form.get('minimum_bid').value ?? '0');
			data.append('appraisal_value', this.form.get('appraisal_value').value);
			data.append('commission', this.form.get('commission').value);
			data.append('featured', this.form.get('featured').value ? '1' : '0');
			data.append('dontshowtimer', this.form.get('dontshowtimer').value ? '1' : '0');
			data.append('video', this.form.get('video').value);
			data.append('video_file', this.form.get('video_file').value);
			data.append('idealista', this.form.get('idealista').value);
			data.append('rrss', this.form.get('rrss').value);
			data.append('repercusion', this.form.get('repercusion').value);
			data.append('mailing', this.form.get('mailing').value);
			data.append('auto', this.form.get('auto').value);
			data.append('juzgado', this.form.get('juzgado').value);
			data.append('meta_title', this.form.get('meta_title').value);
			data.append('meta_description', this.form.get('meta_description').value);
			data.append('meta_keywords', this.form.get('meta_keywords').value);
			data.append('link_rewrite', this.form.get('link_rewrite').value);
			if(this.form.get('description').value==null || this.form.get('description').value==undefined){
				data.append('description', "");
			}else{
				data.append('description', this.form.get('description').value);
			}
			data.append('description_document', this.form.get('description_document').value);
			data.append('description_document_two', this.form.get('description_document_two').value);
			if(this.form.get('technical_specifications').value==null || this.form.get('technical_specifications').value==undefined){
				data.append('technical_specifications', "");
			}else{
				data.append('technical_specifications', this.form.get('technical_specifications').value);
			}
			data.append('technical_document', this.form.get('technical_document').value);
			data.append('technical_document_two', this.form.get('technical_document_two').value);
			if(this.form.get('land_registry').value==null || this.form.get('land_registry').value==undefined){
				data.append('land_registry', "");
			}else{
				data.append('land_registry', this.form.get('land_registry').value);
			}
			data.append('land_registry_document', this.form.get('land_registry_document').value);
			data.append('land_registry_document_two', this.form.get('land_registry_document_two').value);
			if(this.form.get('conditions').value==null || this.form.get('conditions').value==undefined){
				data.append('conditions', "");
			}else{
				data.append('conditions', this.form.get('conditions').value);
			}
			data.append('conditions_document', this.form.get('conditions_document').value);
			data.append('conditions_document_two', this.form.get('conditions_document_two').value);
			this.auctionsService.saveDirectSelling(data)
			.subscribe(data => {
				this.router.navigate(['/direct-sellings']);
				this.utils.showToast('Añadido correctamente');
			}, data => {
				if (data.error.code == 401) {
					this.utils.parseResponseErrors(this.form, data);
					this.utils.showToast('Formulario incorrecto', 'error');
					if(data.error.messages[0].link_rewrite=="validation:used"){
						this.urlUsed = true;
					}
				} else {
					this.utils.showToast(data.error.messages, 'error');
				}
			});

		} else {
			this.utils.showToast('Formulario incorrecto', 'error');
		}
	}

	openAssetModal() {
		this.modalRef = this.modalService.show(this.assetModal, { class: 'modal-lg' });
	}

	titleChange(){
		this.form.patchValue({
			link_rewrite: this.form.get('title').value.toLowerCase().replace(/ /g,'-').replace(/[^\w-]+/g,''),
		});
	}

	editAsHtml1(){
		this.editHtml1 = !this.editHtml1;
	}
	editAsHtml2(){
		this.editHtml2 = !this.editHtml2;
	}
	editAsHtml3(){
		this.editHtml3 = !this.editHtml3;
	}
	editAsHtml4(){
		this.editHtml4 = !this.editHtml4;
	}

  getMaxId(){
    this.dataService.getAuctionMaxId().then((response: Maximo)=>{
        this.maximoId = response;
    });
	}
}
