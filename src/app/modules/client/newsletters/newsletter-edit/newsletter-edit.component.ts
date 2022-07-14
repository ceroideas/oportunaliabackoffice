import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Newsletter, NewsletterStatus, NewsletterTemplate } from 'src/app/shared/models/communication.model';
import { Auction } from 'src/app/shared/models/auction.model';

import { formErrors, minLengthArray } from 'src/app/shared/validators';

@Component({
	selector: 'app-newsletter-edit',
	templateUrl: './newsletter-edit.component.html',
	styleUrls: ['./newsletter-edit.component.scss']
})
export class NewsletterEditComponent implements OnInit {

	public breadcrumbTitle: string = '';
	public title: string = '';

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	public dragAuctionId: number;

	// Form

	public newsletter: Newsletter = {} as any;
	public newsletterId: number;
	public newsletterTemplateId: number = null;
	public newsletterStatus: any = NewsletterStatus;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }
	createForArray(data): FormGroup { return this.fb.group(data); }
	get auctionsArray(): FormArray {
		return this.form.get('auctions') as FormArray;
	}

	// Selectors

	public templates: NewsletterTemplate[] = [];

	// Modals

	@ViewChild('templateModal') templateModal: TemplateRef<any>;
	@ViewChild('auctionModal') auctionModal: TemplateRef<any>;
	public modalRef: BsModalRef;

	public editor = ClassicEditor;
	public ckOptions = null;

	constructor(
		private router: Router,
		private route: ActivatedRoute,
		private modalService: BsModalService,
		public fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService,
		public communicationsService: CommunicationsService
	) {
	}

	ngOnInit(): void {

		this.ckOptions = this.utils.ckOptions;

		this.getNewsletterTemplates();

		this.setFormFields();

		this.initTable();

		this.route.params.subscribe(params => {

			this.newsletterId = params['id'];

			if (this.newsletterId) {
				this.breadcrumbTitle = 'Editar';
				this.title = 'Editar Newsletter';
				this.getNewsletter();
			} else {
				this.breadcrumbTitle = 'Nueva';
				this.title = 'Nueva Newsletter';
			}
		});
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.dtTrigger.next();
	}

	getNewsletter() {

		this.communicationsService.getNewsletter(this.newsletterId)
		.subscribe((data: BaseResponse<Newsletter>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.newsletter = data.response;

				this.form.patchValue(this.newsletter);

				this.auctionsArray.clear();

					this.newsletter.auctions.forEach((auction: any) => {
						this.auctionsArray.push(this.createForArray(auction));
					});
			}

			this.reloadTable();

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
			this.router.navigate(['/newsletters']);
		});
	}

	initTable(): void {

		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			dom: `t`,
			paging: false,
			columns: [
				{
					title: '', orderable: false, data: null, className: 'all',
					render: function (data, type, row, meta) {

						let render = '';

						render += `<a class="mover text-gray pointer">
							<i class="fa fa-arrows-alt"></i>
						</a>`;

						return render;
					}
				},
				{
					title: 'Título', data: 'title',
				},
				{
					title: 'Referencia', data: 'id',
				},
				{
					title: 'Fecha de inicio',
					data: function (row) {
						return formatDate(row.start_date, 'dd/MM/yyyy HH:mm', 'es');
					}
				},
				{
					title: 'Fecha de fin',
					data: function (row) {
						return formatDate(row.end_date, 'dd/MM/yyyy HH:mm', 'es');
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all',
					render: function (data, type, row, meta) {

						let render = '';

						render += `<button class="quitar btn btn-table btn-red">
							<i class="fa fa-trash"></i>
						</button>`;

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: [0,1,2,3,4,5], orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$(row).attr('draggable', 'true');

				$(row).unbind('dragstart');
				$(row).bind('dragstart', evt => {
					this.dragAuctionId = data['id'];
				});

				$(row).unbind('dragend');
				$(row).bind('dragend', evt => {
					this.dragAuctionId = null;
				});

				$(row).unbind('dragover');
				$(row).bind('dragover', evt => {
					evt.preventDefault();
					evt.stopPropagation();
					if (this.dragAuctionId != data['id']) {
						$(row).addClass('highlight');
					}
				});

				$(row).unbind('dragleave');
				$(row).bind('dragleave', evt => {
					evt.preventDefault();
					evt.stopPropagation();
					if (this.dragAuctionId != data['id']) {
						$(row).removeClass('highlight');
					}
				});

				$(row).unbind('drop');
				$(row).bind('drop', evt => {
					evt.preventDefault();
					evt.stopPropagation();
					if (this.dragAuctionId != data['id']) {
						this.swapAuctions(this.dragAuctionId, data['id']);
					}
				});

				$('button.quitar', row).unbind('click');
				$('button.quitar', row).bind('click', () => {
					this.removeAuction(index);
				});

				return row;
			},
			initComplete: function(settings, json) {
			}
		};
	}

	reloadTable() {

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.clear();
			dtInstance.rows.add(this.auctionsArray.value).draw();
		});
	}

	swapAuctions(dragId: number, dropId: number) {

		let array = this.auctionsArray.value;

		let dragIndex = array.findIndex(item => item.id == dragId);
		let dropIndex = array.findIndex(item => item.id == dropId);

		let aux = array[dragIndex];
		array[dragIndex] = array[dropIndex];
		array[dropIndex] = aux;

		this.auctionsArray.clear();

		array.forEach(item => {
			this.auctionsArray.push(this.createForArray(item));
		});

		this.reloadTable();
	}

	addAuction(auction: Auction) {
		this.auctionsArray.push(this.createForArray(auction));
		this.reloadTable();
	}

	removeAuction(id: number) {
		this.auctionsArray.removeAt(id);
		this.reloadTable();
	}

	getNewsletterTemplates() {

		this.dataService.getNewsletterTemplates()
		.then((val: NewsletterTemplate) => {
			this.templates = val['response'];
			// N.B.: this is because when retrieving selector data again
			// the selector strangely autoselects itself without updating the form
			this.templates.unshift({ id: null, name: '' } as NewsletterTemplate);
		});
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			status_id: [''],
			template_id: ['', [Validators.required]],
			subject: ['', [Validators.required]],
			sender: ['', [Validators.required]],
			email: ['', [Validators.required]],
			content: ['', [Validators.required]],
			send_date: [''],
			send_time: [''],
			auctions: this.fb.array([], minLengthArray(1)),
		});
	}

	saveNewsletter(status = null) {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.newsletter);

			const data = new FormData();

			let sendDate = this.form.get('send_date').value;
			let sendTime = this.form.get('send_time').value;
			sendTime = sendTime ? sendTime : '00:00';
			sendDate = sendDate ? `${sendDate} ${sendTime}` : '';

			data.append('template_id', this.form.get('template_id').value);
			data.append('subject', this.form.get('subject').value);
			data.append('sender', this.form.get('sender').value);
			data.append('email', this.form.get('email').value);
			data.append('content', this.form.get('content').value);
			data.append('send_date', `${sendDate}`);

			this.auctionsArray.value.forEach(value => {
				data.append('auctions[]', value.id);
			});

			if (!this.newsletterId) {

				data.append('status_id', status ? status : '2');

				this.communicationsService.saveNewsletter(data)
				.subscribe(data => {
					this.router.navigate(['/newsletters']);
					this.utils.showToast('Añadido correctamente');
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});

			} else {

				data.append('id', this.newsletterId.toString());
				data.append('status_id', status ? status : this.form.get('status_id').value);

				this.communicationsService.editNewsletter(data, this.newsletterId)
				.subscribe(data => {
					this.router.navigate(['/newsletters']);
					this.utils.showToast('Editado correctamente');
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});
			}

		} else {
			this.utils.showToast('Formulario incorrecto', 'error');
		}
	}

	openTemplateModal() {
		this.modalRef = this.modalService.show(this.templateModal, { class: 'modal-lg' });
	}

	openAuctionModal() {
		this.modalRef = this.modalService.show(this.auctionModal, { class: 'modal-xl' });
	}
}
