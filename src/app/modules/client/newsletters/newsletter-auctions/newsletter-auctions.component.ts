import { Component, OnInit, TemplateRef, ViewChild, OnChanges, SimpleChanges, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { formatDate } from '@angular/common';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Auction, AuctionStatus } from 'src/app/shared/models/auction.model';

import { minLengthArray } from 'src/app/shared/validators';

@Component({
	selector: 'app-newsletter-auctions',
	templateUrl: './newsletter-auctions.component.html',
	styleUrls: ['./newsletter-auctions.component.scss']
})
export class NewsletterAuctionsComponent implements OnInit {

	@Input() selected!: Array<number>;
	@Output() success: EventEmitter<any> = new EventEmitter();
	@Output() close: EventEmitter<any> = new EventEmitter();

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';
	private filter_start_date: any;
	private filter_end_date: any;

	constructor(
		public utils: UtilsService,
		public dataService: DataService,
		public auctionsService: AuctionsService
	) {
	}

	ngOnInit(): void {
		this.initTable();
	}

	ngOnChanges(changes: SimpleChanges): void {
		this.selected = changes.selected.currentValue.map(item => item.id);
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
				url: endpoint('auctions_dt', { selected: this.selected.join(',') }),
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
					title: 'Estado', className: 'all',
					data: function (row) {

						let styles = that.auctionsService.auctionStatus(row);

						return `<div class="pill pill-${styles.color}">${row.status}</div>`;
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function (data, type, row, meta) {

						let render = '';

						render += `<button class="anadir btn btn-table btn-green" title="Añadir">
							<i class="fa fa-plus"></i>
						</button>`;

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: -1, orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.anadir', row).unbind('click');
				$('button.anadir', row).bind('click', () => {
					that.sendAuction(data as Auction);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Tab filters

				let filters = [
					{ name: 'Todos', value: 'all' },
					{ name: 'Borradores', value: 'draft' },
					{ name: 'En curso', value: 'ongoing' },
					{ name: 'Finalizadas', value: 'finished' },
					{ name: 'Archivados', value: 'archived' },
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

				let table_id = '#subastas-newsletter';

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

		let params: any = { selected: this.selected.join(',') };

		switch (this.filter_tab) {
			case 'draft':
				params.auction_status_id = AuctionStatus.DRAFT;
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

	sendAuction(auction: Auction) {
		this.success.emit(auction);
	}
}
