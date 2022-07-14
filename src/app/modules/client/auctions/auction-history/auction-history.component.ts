import { Component, OnInit, OnChanges, SimpleChanges, Input, Output, EventEmitter, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';
import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Auction, AuctionStatus } from 'src/app/shared/models/auction.model';

@Component({
	selector: 'app-auction-history',
	templateUrl: './auction-history.component.html',
	styleUrls: ['./auction-history.component.scss']
})
export class AuctionHistoryComponent implements OnInit, OnChanges {

	public lastBidLabel: string = '';
	public timeLeftLabel: string = '';

	@Input() auctionId!: number;
	@Output() close: EventEmitter<any> = new EventEmitter();

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Auction

	public auction: Auction = {} as any;
	private countdownInterval;
	public time_left: string = null;

	public images: any = [];
	public imageIndex: number = 0;

	constructor(
		public utils: UtilsService,
		public auctionsService: AuctionsService
	) {
	}

	ngOnInit(): void {

		this.countdown();

		this.countdownInterval = setInterval(() => this.countdown(), 1000);

		this.initTable();
	}

	ngOnChanges(changes: SimpleChanges): void {

		this.reloadTable();
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
			dom: `tip`,
			ajax: {
				url: endpoint('auction_history_dt', { id: this.auctionId }),
				type: 'get',
				headers: {
					Authorization: sessionStorage.getItem('token')
				},
				dataSrc: function (json) {
					that.utils.logResponse(json);

					that.auction = json['response']['auction'];

					that.images = that.auction.images;

					return json['response']['bids'];
				}
			},
			columns: [
				{
					title: 'Fecha de puja',
					data: function (row) {
						return formatDate(row.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');
					}
				},
				{
					title: 'Nombre de usuario', data: 'username',
				},
				{
					title: 'Precio de puja',
					data: function (row) {
						return row.import + " €";
					}
				},
				// {
				// 	title: 'Tipo de puja', data: 'type',
				// },
				{
					title: '', orderable: false, className: 'not-filterable',
					data: function (row) {

						let pillColor, pillIcon;

						if (row.is_best_bid == 1) {
							pillColor = 'cyan'; pillIcon = 'fa fa-trophy';
						} else {
							pillColor = 'red'; pillIcon = 'fa fa-gavel';
						}

						return `<i class="circle-pill pill-${pillColor} ${pillIcon} mr-3"></i>`;
					}
				},
			],
			columnDefs: [
				{ targets: -1, orderable: false },
			],
			order: [2, 'desc'],
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#subasta-pujas';

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

		if (!this.dtElement) { return; }

		let newUrl = endpoint('auction_history_dt', { id: this.auctionId });

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	countdown() {

		this.timeLeftLabel = 'Tiempo restante';
		this.lastBidLabel = 'Última puja';

		if (this.auction) {

			if (this.auction.auction_status_id == AuctionStatus.ONGOING) {

				this.time_left = this.utils.timeDiffString(`${this.auction.end_date}`);
			}

			this.timeLeftLabel = this.time_left ? 'Tiempo restante' : 'Subasta finalizada';
			this.lastBidLabel = this.time_left ? 'Última puja' : 'Puja ganadora';

			if (!this.time_left) {
				clearInterval(this.countdownInterval);
			}
		}
	}
}
