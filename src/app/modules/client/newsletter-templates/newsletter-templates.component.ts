import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { Blog } from 'src/app/shared/models/communication.model';

@Component({
	selector: 'app-newsletter-templates',
	templateUrl: './newsletter-templates.component.html',
	styleUrls: ['./newsletter-templates.component.scss']
})
export class NewsletterTemplatesComponent implements OnInit {

	public newsletterTemplateId: number;

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals

	@ViewChild('newsletterTemplateModal') newsletterTemplateModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private deleteId: number;

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public communicationsService: CommunicationsService
	) {
	}

	ngOnInit(): void {
		this.initTable();
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.dtTrigger.next();
	}

	initTable(): void {

		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('newsletter_templates_dt'),
				type: 'get',
				headers: {
					Authorization: sessionStorage.getItem('token')
				},
				dataSrc: function (json) {
					return json['response'];
				}
			},
			columns: [
				{
					title: 'Nombre plantilla', data: 'name',
				},
				{
					title: 'Asunto', data: 'subject',
				},
				{
					title: 'Remitente', data: 'sender',
				},
				{
					title: 'Email remitente', data: 'email',
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function () {

						let render = '';

						render += `<button class="editar btn btn-table btn-navy" title="Editar">
							<i class="fa fa-pencil-alt"></i>
						</button>`;

						render += `<button class="borrar btn btn-table btn-red" title="Borrar">
							<i class="fa fa-trash"></i>
						</button>`;

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: -1, orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					this.openModal(data['id']);
				});

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					this.confirmDelete(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#plantillas-newsletter';

				that.utils.addColumnFilters(this.api(), table_id);

				that.utils.hideSearchInputs(
					this.api().columns().responsiveHidden().toArray(), table_id
				);

				$(table_id).on('responsive-resize', function (e, datatable, columns) {
					that.utils.hideSearchInputs(columns, table_id);
				});
			}
		};
	}

	reloadTable() {

		let newUrl = endpoint('newsletter_templates_dt');

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	openModal(id?: number) {
		this.newsletterTemplateId = id;
		this.modalRef = this.modalService.show(this.newsletterTemplateModal, { class: 'modal-lg' });
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteNewsletterTemplate() {

		this.communicationsService.deleteNewsletterTemplate(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('La plantilla para newsletter ha sido borrada');
			this.modalRef.hide();
		});
	}

	download(type) {

		this.utils.showToast(`Se está exportando en formato ${type}`);

		this.communicationsService.newsletterTemplatesExport(type)
		.subscribe(data => {
			this.utils.downloadFile(data, type, 'Plantillas de newsletters');
		});
	}
}
