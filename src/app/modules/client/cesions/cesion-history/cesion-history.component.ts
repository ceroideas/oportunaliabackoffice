import { Component, OnInit, OnChanges, SimpleChanges, Input, Output, EventEmitter, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';
import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Auction, AuctionStatus } from 'src/app/shared/models/auction.model';

@Component({
  selector: 'app-cesion-history',
  templateUrl: './cesion-history.component.html',
  styleUrls: ['./cesion-history.component.scss']
})
export class CesionHistoryComponent implements OnInit {

  public lastBidLabel: string = '';
	public timeLeftLabel: string = '';

	@Input() cesionId!: number;
	@Output() close: EventEmitter<any> = new EventEmitter();
	@Output() success: EventEmitter<any> = new EventEmitter();

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals

	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private validateId: number;

	// Auction

	public cesion: Auction = {} as any;
	private countdownInterval;
	public time_left: string = null;

	public images: any = [];
	public imageIndex: number = 0;

  constructor(
		private modalService: BsModalService,
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
				url: endpoint('cesion_history_dt', { id: this.cesionId }),
				type: 'get',
				headers: {
					Authorization: sessionStorage.getItem('token')
				},
				dataSrc: function (json) {
					that.utils.logResponse(json);

					that.cesion = json['response']['auction'];

					that.images = that.cesion.images;

					return json['response']['offers'];
				}
			},
			columns: [
				{
					title: 'Fecha de oferta',
					data: function (row) {
						return formatDate(row.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');
					}
				},
				{
					title: 'Nombre de usuario', data: 'username',
				},
				{
					title: 'Precio de oferta',
					data: function (row) {
						return row.import + " €";
					}
				},
				{
					title: '', orderable: false, data: null, className: 'all not-filterable',
					render: function (data, type, row, meta) {

						switch (row.status) {
							case 1: return `<div class="pill pill-green">Aceptada</div>`; break;
							case 2: return `<div class="pill pill-red">Rechazada</div>`; break;
							default: return `<button class="validar btn btn-table btn-navy m-0" title="Validar">
								<i class="fa fa-check"></i>
							</button>`;
						}
					}
				},
			],
			columnDefs:[
				{ targets: -1, orderable: false },
			],
			order: [[2, 'desc']],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.validar', row).unbind('click');
				$('button.validar', row).bind('click', () => {
					this.confirmValidateOffer(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#cesion-ofertas';

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

		let newUrl = endpoint('cesion_history_dt', { id: this.cesionId });

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	countdown() {

		this.timeLeftLabel = 'Tiempo restante';
		this.lastBidLabel = 'Última oferta';

		if (this.cesion) {

			if (this.cesion.auction_status_id == AuctionStatus.ONGOING) {

				this.time_left = this.utils.timeDiffString(`${this.cesion.end_date}`);
			}

			this.timeLeftLabel = this.time_left ? 'Tiempo restante' : 'Venta finalizada';
			this.lastBidLabel = this.time_left ? 'Última oferta' : 'Oferta ganadora';

			if (!this.time_left) {
				clearInterval(this.countdownInterval);
			}
		}
	}

	confirmValidateOffer(id) {
		this.validateId = id;
		this.modalRef = this.modalService.show(this.confirmValidateModal, { class: 'modal-md' });
	}

	validateOffer(status: number) {

		this.auctionsService.validateOffer(this.validateId, status)
		.subscribe(data => {
			if (status == 1) {
				this.utils.showToast('La oferta ha sido aceptada');
			} else if (status == 2) {
				this.utils.showToast('La oferta ha sido rechazada');
			}
			this.success.emit();
			this.modalRef.hide();
			this.reloadTable();
		});
	}
}
