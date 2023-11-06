import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';

import { AuctionStatus } from 'src/app/shared/models/auction.model';

@Component({
	selector: 'app-auctions',
	templateUrl: './auctions.component.html',
	styleUrls: ['./auctions.component.scss']
})
export class AuctionsComponent implements OnInit {

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';
	private filter_start_date: any;
	private filter_end_date: any;

	// Modals

	@ViewChild('historyModal') historyModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private auctionId: number;
	private deleteId: number;

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public auctionsService: AuctionsService
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
				url: endpoint('auctions_dt'),
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
					title: 'id', data: 'id',
				},
				{
					title: 'Referencia', data: 'auto',
				},
        {
					title: 'Título', data: 'title',
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
					title: 'Precio mínimo',
					data: function (row) {
						return row.start_price + " €";
					}
				},
				{
					title: 'Mejor puja',
					data: function (row) {
						return row.max_bid ? row.max_bid + " €" : '';
					}
				},
				{
					title: 'Nº de pujas', data: 'bids'
				},
				{
					title: 'Destacado', className: 'all text-center not-filterable',
					data: function (row) {

						let color = row.featured ? 'cyan' : 'gray';

						return `<div class="destacar text-${color} pointer">
							<i class="fa fa-star"></i>
						</div>`;
					}
				},
				{
					title: 'Asignado', className: 'all text-center not-filterable',
					data: function (row) {

						let color = row.asignado ? 'cyan' : 'gray';

						return `<div class="asignar text-${color} pointer">
							<i class="fa fa-star"></i>
						</div>`;
					}
				},
				{
					title: 'Estado', className: 'all text-center',
					data: function (row) {

						let styles = that.auctionsService.auctionStatus(row);

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

						render += `<button class="historial btn btn-table btn-navy" title="Ver historial de pujas">
							<i class="fa fa-history"></i>
						</button>`;

						render += `<button class="duplicar btn btn-table btn-navy" title="Duplicar">
							<i class="fa fa-copy"></i>
						</button>`;

						if (row.auction_status_id == AuctionStatus.DRAFT)
						{
							render += `<button class="borrar btn btn-table btn-red" title="Borrar">
								<i class="fa fa-trash"></i>
							</button>`;
						}

						switch (row.auction_status_id)
						{
							case AuctionStatus.FINISHED:
							case AuctionStatus.ARCHIVED:
								render += `<button class="informe btn btn-table btn-green" title="Generar informe final">
									<i class="fa fa-file-download"></i>
								</button>`;
								break;
						}

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: -1, orderable: false },
			],
			order: [[1, 'desc']],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					that.router.navigate(['/auctions', data['id'], 'edit']);
				});

				$('button.historial', row).unbind('click');
				$('button.historial', row).bind('click', () => {
					this.showHistory(data['id']);
				});

				$('button.duplicar', row).unbind('click');
				$('button.duplicar', row).bind('click', () => {
					this.duplicateAuction(data['id']);
				});

				$('.destacar', row).unbind('click');
				$('.destacar', row).bind('click', () => {
					$('.destacar', row).addClass('rotating');
					$('.destacar i', row).removeClass('fa-star');
					$('.destacar i', row).addClass('fa-sync');
					this.featureAuction(data['id']);
				});

				$('.asignar', row).unbind('click');
				$('.asignar', row).bind('click', () => {
					$('.asignar', row).addClass('rotating');
					$('.asignar i', row).removeClass('fa-star');
					$('.asignar i', row).addClass('fa-sync');
					this.asignarAuction(data['id']);
				});

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					this.confirmDelete(data['id']);
				});

				$('button.informe', row).unbind('click');
				$('button.informe', row).bind('click', () => {
					this.finalReport(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Tab filters

				let filters = [
					{ name: 'Todos', value: 'all' },
					{ name: 'Borradores', value: 'draft' },
					{ name: 'Próximamente', value: 'soon' },
					{ name: 'En curso', value: 'ongoing' },
					{ name: 'Finalizadas', value: 'finished' },
					{ name: 'Archivadas', value: 'archived' },
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

				$('.dataTables_filters').append(
					$('<div>', { class: 'dataTables_filter_input ml-3' })
						.append(`<input type="date"
							data-filter="start_date"
							title="Fecha de inicio"
						>`)
				);

				$('[data-filter=start_date]').change(evt => {
					that.filter_start_date = $(evt.target).val();
					that.reloadTable();
				});

				$('.dataTables_filters').append(
					$('<div>', { class: 'dataTables_filter_input ml-3' })
						.append(`<input type="date"
							data-filter="end_date"
							title="Fecha de fin"
						>`)
				);

				$('[data-filter=end_date]').change(evt => {
					that.filter_end_date = $(evt.target).val();
					that.reloadTable();
				});

				// Column filters

				let table_id = '#subastas';

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
			case 'draft':
				params.auction_status_id = AuctionStatus.DRAFT;
				break;
			case 'soon':
				params.auction_status_id = AuctionStatus.SOON;
				break;
			case 'ongoing':
				params.auction_status_id = AuctionStatus.ONGOING;
				break;
			case 'finished':
				params.auction_status_id = AuctionStatus.FINISHED;
				break;
			case 'archived':
				params.auction_status_id = AuctionStatus.ARCHIVED;
				break;
		}

		if (this.filter_start_date) {
			params.start_date = this.filter_start_date;
		}

		if (this.filter_end_date) {
			params.end_date = this.filter_end_date;
		}

		let newUrl = endpoint('auctions_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	showHistory(id) {
		this.auctionId = id;
		this.modalRef = this.modalService.show(this.historyModal, { class: 'modal-lg' });
	}

	featureAuction(id) {

		this.auctionsService.featureAuction(id)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('Cambiado el valor de destacado');
		});
	}

	asignarAuction(id) {

		this.auctionsService.asignarAuction(id)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('Cambiado el valor de asignado');
		});
	}


	duplicateAuction(id: number){

		this.auctionsService.duplicateAuction(id)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('Subasta duplicada');
		});

	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteAuction() {

		this.auctionsService.deleteAuction(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('La subasta ha sido borrada');
			this.modalRef.hide();
		});
	}

	download(type: string) {

		this.utils.showToast(`Se está exportando en formato ${type}`);

		this.auctionsService.auctionsExport(type)
		.subscribe(data => {
			this.utils.downloadFile(data, type, 'Subastas');
		});
	}

	finalReport(id: number) {

		this.utils.showToast(`Se está exportando el informe final`);

		this.auctionsService.auctionFinalReport(id)
		.subscribe(data => {
			this.utils.downloadFile(data, 'pdf', 'Informe final de subasta '+id);
		});
	}
}
