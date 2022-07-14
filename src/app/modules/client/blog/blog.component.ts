import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { Blog, BlogStatus } from 'src/app/shared/models/communication.model';

@Component({
	selector: 'app-blog',
	templateUrl: './blog.component.html',
	styleUrls: ['./blog.component.scss']
})
export class BlogComponent implements OnInit {

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';

	// Modals

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
				url: endpoint('blog_dt'),
				type: 'get',
				headers: {
					Authorization: sessionStorage.getItem('token')
				},
				dataSrc: function (json) {
					that.utils.logResponse(json);
					return json['response'];
				}
			},
			columns: [
				{
					title: 'Fecha de creación',
					data: function (row) {
						return formatDate(row.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');
					}
				},
				{
					title: 'Fecha de publicación',
					data: function (row) {
						if (row.publish_date) {
							return formatDate(row.publish_date, 'dd/MM/yyyy HH:mm:ss', 'es');
						} else if (row.show_date) {
							return formatDate(row.show_date, 'dd/MM/yyyy HH:mm:ss', 'es');
						} else {
							return 'Sin publicar';
						}
					}
				},
				{
					title: 'Título entrada', data: 'title',
				},
				{
					title: 'Nº de visitas', data: 'views',
				},
				{
					title: 'Estado', className: 'all',
					data: function (row) {

						let styles = that.communicationsService.blogStatus(row);

						return `<div class="pill pill-${styles.color}">${row.status}</div>`;
					}
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
					that.router.navigate(['/blog', data['id'], 'edit']);
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
					{ name: 'Publicadas', value: 'published' },
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

				// Column filters

				let table_id = '#blog';

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
			case 'published':
				params.status_id = BlogStatus.PUBLISHED;
				break;
			case 'scheduled':
				params.status_id = BlogStatus.SCHEDULED;
				break;
			case 'drafts':
				params.status_id = BlogStatus.DRAFT;
				break;
		}

		let newUrl = endpoint('blog_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteBlog() {

		this.communicationsService.deleteBlog(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('La entrada de blog ha sido borrada');
			this.modalRef.hide();
		});
	}

	download(type) {

		this.utils.showToast(`Se está exportando en formato ${type}`);

		this.communicationsService.blogExport(type)
		.subscribe(data => {
			this.utils.downloadFile(data, type, 'Entradas de blog');
		});
	}
}
