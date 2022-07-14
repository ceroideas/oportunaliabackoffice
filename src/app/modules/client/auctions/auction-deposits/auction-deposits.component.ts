import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Auction } from 'src/app/shared/models/auction.model';

@Component({
	selector: 'app-auction-deposits',
	templateUrl: './auction-deposits.component.html',
	styleUrls: ['./auction-deposits.component.scss']
})
export class AuctionDepositsComponent implements OnInit {

	public createdAt: string = '';
	public auctionStatus: string = '';
	public auctionStatusColor: string = '';

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals

	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private validateId: number;

	// Auction

	public auctionId: number;
	public auction: Auction = {} as any;

	constructor(
		private router: Router,
		private route: ActivatedRoute,
		public utils: UtilsService,
		public dataService: DataService,
		private modalService: BsModalService,
		public auctionsService: AuctionsService
	) {
	}

	ngOnInit(): void {

		this.route.params.subscribe(params => {

			if (!params['id']) { this.router.navigate(['/auctions']); }

			this.auctionId = params['id'];

			this.initTable();

			this.getAuction();
		});
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.dtTrigger.next();
	}

	getAuction() {

		this.auctionsService.getAuction(this.auctionId)
		.subscribe((data: BaseResponse<Auction>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.auction = data.response;

				this.createdAt = formatDate(this.auction.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');

				let styles = this.auctionsService.auctionStatus(this.auction);
				this.auctionStatus = styles.text;
				this.auctionStatusColor = 'text-'+styles.color;
			}

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
			this.router.navigate(['/auctions']);
		});
	}

	initTable(): void {

		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('auction_deposits_dt', { id: this.auctionId }),
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
					title: 'Fecha',
					data: function (row) {
						return formatDate(row.created_at, 'dd/MM/yyyy HH:mm', 'es');
					}
				},
				{
					title: 'Nombre usuario', data: 'username',
				},
				{
					title: 'Nombre', data: 'firstname',
				},
				{
					title: 'Apellidos', data: 'lastname'
				},
				{
					title: 'NIF/CIF', data: 'document_number'
				},
				{
					title: 'Depósito', className: 'all',
					data: function (row) {
						return row.deposit + " €";
					}
				},
				{
					title: 'Justificante', className: 'all text-center not-filterable',
					data: function (row) {

						let render = '';

						render += `<button class="justificante btn btn-table btn-navy" title="Ver justificante">
							<i class="fa fa-file-download"></i>
						</button>`;

						return render;
					}
				},
				{
					title: 'Validar depósito', className: 'all text-center not-filterable',
					data: function (row, data, type) {

						switch (row.status) {
							case 1: return `<div class="pill pill-green" title="Cambiar validación">Validado</div>`; break;
							case 2: return `<div class="pill pill-red" title="Cambiar validación">No validado</div>`; break;
							default: return `<button class="validar btn btn-table btn-navy m-0" title="Validar">
								<i class="fa fa-check"></i>
							</button>`;
						}
					}
				},
			],
			columnDefs:[
				{ targets: [-1, -2], orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.justificante', row).unbind('click');
				$('button.justificante', row).bind('click', () => {
					window.open(data['document']['path'], '_blank').focus();
				});

				$('.validar', row).unbind('click');
				$('.validar', row).bind('click', () => {
					this.confirmValidateDeposit(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#subasta-depositos';

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

		let newUrl = endpoint('auction_deposits_dt', { id: this.auctionId });

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	confirmValidateDeposit(id) {
		this.validateId = id;
		this.modalRef = this.modalService.show(this.confirmValidateModal, { class: 'modal-md' });
	}

	validateDeposit(status: number) {

		this.auctionsService.validateDeposit(this.validateId, status)
		.subscribe(data => {
			if (status == 1) {
				this.utils.showToast('El depósito ha sido validado');
			} else if (status == 2) {
				this.utils.showToast('El depósito ha sido invalidado');
			}
			this.reloadTable();
			this.modalRef.hide();
		});
	}
}
