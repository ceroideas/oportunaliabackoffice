import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { Newsletter, NewsletterStatus, NewsletterTemplate } from 'src/app/shared/models/communication.model';

@Component({
	selector: 'app-newsletters',
	templateUrl: './newsletters.component.html',
	styleUrls: ['./newsletters.component.scss']
})
export class NewslettersComponent implements OnInit {

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';
	private filter_newsletter_template: any;

	// Selectors

	public newsletterTemplates: NewsletterTemplate[] = [];

	// Modals

	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private deleteId: number;

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public dataService: DataService,
		public communicationsService: CommunicationsService
	) {
	}

	ngOnInit(): void {

		this.dataService.getNewsletterTemplates().then((val: NewsletterTemplate) => {
			this.newsletterTemplates = val['response'];
		});

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
				url: endpoint('newsletters_dt'),
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
					title: 'Fecha de creación',
					data: function (row) {
						return formatDate(row.created_at, 'dd/MM/yyyy', 'es');
					}
				},
				{
					title: 'Fecha de envío',
					data: function (row) {
						if (row.sent_date) {
							return formatDate(row.sent_date, 'dd/MM/yyyy HH:mm:ss', 'es');
						} else if (row.send_date) {
							return formatDate(row.send_date, 'dd/MM/yyyy HH:mm:ss', 'es');
						} else {
							return 'Sin enviar';
						}
					}
				},
				{
					title: 'Plantilla', data: 'template',
				},
				{
					title: 'Estado', className: 'all',
					data: function (row) {

						let styles = that.communicationsService.newsletterStatus(row);

						return `<div class="pill pill-${styles.color}">${row.status}</div>`;
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function (data, type, row, meta) {

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
					that.router.navigate(['/newsletters', data['id'], 'edit']);
				});

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					this.confirmDelete(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Tab filters

				let filters = [
					{ name: 'Todas', value: 'all' },
					{ name: 'Enviadas', value: 'sent' },
					{ name: 'Programadas', value: 'scheduled' },
					{ name: 'Borradores', value: 'drafts' },
				];

				filters.forEach(filter => {
					$('.dataTables_filter_tabs').append(
						`<div class="dataTables_filterButton" data-value="${ filter.value }">
							${ filter.name }
						</div>`
					);
				});

				$('.dataTables_filterButton[data-value=all]').addClass('active');

				$('.dataTables_filterButton').click(evt => {

					$('.dataTables_filterButton').removeClass('active');

					let elem = $(evt.target);
					elem.addClass('active');
					that.filter_tab = elem.data('value');

					that.reloadTable();
				});

				// Select filters

				let newsletter_template = $(`<select class="text-darkgray"
						data-filter="newsletter_template"
						style="width:12rem"
					></select>`);

				newsletter_template.append(`<option value="" class="text-gray">
					Plantilla
				</option>`);

				that.newsletterTemplates.forEach(option => {
					newsletter_template.append(`<option value="${ option.id }">
						${ option.name }
					</option>`);
				});

				$('.dataTables_filters').append(
					$('<div>', { class: 'dataTables_filter_select' })
						.append(newsletter_template)
						.append(`<i class="icon fa fa-user-tag text-gray ml-2"></i>`)
				);

				$('[data-filter=newsletter_template]').change(evt => {

					let elem = $(evt.target).find('option:selected');
					that.filter_newsletter_template = elem.val();

					that.reloadTable();
				});

				// Column filters

				let table_id = '#newsletters';

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

		let params: any = {};

		switch (this.filter_tab) {
			case 'sent':
				params.status_id = NewsletterStatus.SENT;
				break;
			case 'scheduled':
				params.status_id = NewsletterStatus.SCHEDULED;
				break;
			case 'drafts':
				params.status_id = NewsletterStatus.DRAFT;
				break;
		}

		if (this.filter_newsletter_template) {
			params.template_id = this.filter_newsletter_template;
		}

		let newUrl = endpoint('newsletters_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteNewsletter() {

		this.communicationsService.deleteNewsletter(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('La newsletter ha sido borrada');
			this.modalRef.hide();
		});
	}

	download(type) {

		this.utils.showToast(`Se está exportando en formato ${type}`);

		this.communicationsService.newslettersExport(type)
		.subscribe(data => {
			this.utils.downloadFile(data, type, 'Newsletters');
		});
	}
}
