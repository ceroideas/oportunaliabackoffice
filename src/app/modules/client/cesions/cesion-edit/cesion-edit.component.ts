import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Asset } from 'src/app/shared/models/asset.model';
import { Auction } from 'src/app/shared/models/auction.model';

import { formErrors, lte, dateCoherence } from 'src/app/shared/validators';

@Component({
  selector: 'app-cesion-edit',
  templateUrl: './cesion-edit.component.html',
  styleUrls: ['./cesion-edit.component.scss']
})
export class CesionEditComponent implements OnInit {

  public createdAt: string = '';
	public cesionStatus: string = '';
	public cesionStatusColor: string = '';

	editHtml1 = false;
	editHtml2 = false;
	editHtml3 = false;
	editHtml4 = false;

	urlUsed = false;
	// Form

	public cesionId: number;
	public cesion: Auction = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }

	public editor = ClassicEditor;
	public ckOptions = null;

	// Selectors

	public assets: Asset[] = [];

	// Modals

	@ViewChild('assetModal') assetModal: TemplateRef<any>;
	public modalRef: BsModalRef;

	private MAX_FILE_SIZE: number = 8000000;

  constructor(
		private router: Router,
		private route: ActivatedRoute,
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

		this.setFormFields();

		this.route.params.subscribe(params => {

			if (!params['id']) { this.router.navigate(['/cesions']); }

			this.cesionId = params['id'];

			this.getAuction();
		});
  }

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {
	}

	getAuction() {

		this.auctionsService.getAuction(this.cesionId)
		.subscribe((data: BaseResponse<Auction>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.cesion = data.response;

				this.createdAt = formatDate(this.cesion.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');

				let styles = this.auctionsService.auctionStatus(this.cesion);
				this.cesionStatus = styles.text;
				this.cesionStatusColor = 'text-'+styles.color;

				let startDate = this.utils.extractDateFrom(this.cesion.start_date);
				let startTime = this.utils.extractTimeFrom(this.cesion.start_date);
				this.cesion.start_date = startDate;
				this.cesion.start_time = startTime;

				let endDate = this.utils.extractDateFrom(this.cesion.end_date);
				let endTime = this.utils.extractTimeFrom(this.cesion.end_date);
				this.cesion.end_date = endDate;
				this.cesion.end_time = endTime;
			}

			this.form.patchValue(this.cesion);

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
			this.router.navigate(['/cesions']);
		});
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
			auction_status_id: [''],
			title: ['', [Validators.required]],
			active_id: ['', [Validators.required]],
			start_date: ['', [Validators.required]],
			start_time: ['', [Validators.required]],
			end_date: ['', [Validators.required]],
			end_time: ['', [Validators.required]],
			start_price: ['', [Validators.required]],
			minimum_bid: [''],
			deposit: [''],
			appraisal_value: ['', [Validators.required]],
			commission: ['', [Validators.required, lte(100)]],
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

	saveCesion(status = null) {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.cesion);

			const data = new FormData();

			let startDate = this.form.get('start_date').value;
			let startTime = this.form.get('start_time').value + ':00';

			let endDate = this.form.get('end_date').value;
			let endTime = this.form.get('end_time').value + ':00';

			data.append('id', this.cesion.id.toString());
			data.append('auction_status_id', status ? status : this.form.get('auction_status_id').value);
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
			data.append('link_rewrite', this.form.get('link_rewrite').value);
			if(this.form.get('video').value!=null && this.form.get('video').value!="null" && this.form.get('video').value!=undefined){
				data.append('video', this.form.get('video').value);
			}else{
				data.append('video', "");
			}
			if(this.form.get('video_file').value!=null && this.form.get('video_file').value!="null" && this.form.get('video_file').value!=undefined){
				data.append('video_file', this.form.get('video_file').value);
			}else{
				data.append('video_file', "");
			}
			if(this.form.get('idealista').value!=null && this.form.get('idealista').value!="null" && this.form.get('idealista').value!=undefined){
				data.append('idealista', this.form.get('idealista').value);
			}else{
				data.append('idealista', "");
			}
			if(this.form.get('rrss').value!=null && this.form.get('rrss').value!="null" && this.form.get('rrss').value!=undefined){
				data.append('rrss', this.form.get('rrss').value);
			}else{
				data.append('rrss', "");
			}
			if(this.form.get('repercusion').value!=null && this.form.get('repercusion').value!="null" && this.form.get('repercusion').value!=undefined){
				data.append('repercusion', this.form.get('repercusion').value);
			}else{
				data.append('repercusion', "");
			}
			if(this.form.get('mailing').value!=null && this.form.get('mailing').value!="null" && this.form.get('mailing').value!=undefined){
				data.append('mailing', this.form.get('mailing').value);
			}else{
				data.append('mailing', "");
			}
			if(this.form.get('auto').value!=null && this.form.get('auto').value!="null" && this.form.get('auto').value!=undefined){
				data.append('auto', this.form.get('auto').value);
			}else{
				data.append('auto', "");
			}
			if(this.form.get('juzgado').value!=null && this.form.get('juzgado').value!="null" && this.form.get('juzgado').value!=undefined){
				data.append('juzgado', this.form.get('juzgado').value);
			}else{
				data.append('juzgado', "");
			}
			if(this.form.get('meta_title').value!=null && this.form.get('meta_title').value!="null" && this.form.get('meta_title').value!=undefined){
				data.append('meta_title', this.form.get('meta_title').value);
			}else{
				data.append('meta_title', "");
			}
			if(this.form.get('meta_description').value!=null && this.form.get('meta_description').value!="null" && this.form.get('meta_description').value!=undefined){
				data.append('meta_description', this.form.get('meta_description').value);
			}else{
				data.append('meta_description', "");
			}
			if(this.form.get('meta_keywords').value!=null && this.form.get('meta_keywords').value!="null" && this.form.get('meta_keywords').value!=undefined){
				data.append('meta_keywords', this.form.get('meta_keywords').value);
			}else{
				data.append('meta_keywords', "");
			}
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

			this.auctionsService.editCesion(data, this.cesionId)
			.subscribe(data => {
				this.router.navigate(['/cesions']);
				this.utils.showToast('Editado correctamente');
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

	gotoPublic() {
		window.open(this.cesion.public_path, '_blank').focus();
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

	deleteDocument(document: any) {

		this.auctionsService.deleteDocument(this.cesionId,document)
		.subscribe(data => {
			this.utils.showToast('El documento ha sido borrado');
		});
	}
}
