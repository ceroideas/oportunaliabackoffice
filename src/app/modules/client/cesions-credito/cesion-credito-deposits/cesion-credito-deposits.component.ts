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
import { Auction } from 'src/app/shared/models/auction.model';

@Component({
	selector: 'app-cesion-credito-deposits',
	templateUrl: './cesion-credito-deposits.component.html',
	styleUrls: ['./cesion-credito-deposits.component.scss']
})
export class CesionCreditoDepositsComponent implements OnInit {

	// DataTables
	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals
	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private validateId: number;

	// Data
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
		this.auctionId = this.route.snapshot.params['id'];
		this.getAuction();
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.initTable();
	}

	getAuction() {
		/*this.dataService.get(endpoint('cesions_credito_get', { id: this.auctionId }))
			.subscribe((response: any) => {
				this.auction = response.response;
			});*/
	}

	initTable(): void {

		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('cesion_credito_deposits_dt', { id: this.auctionId }),
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

				let table_id = '#cesion-credito-depositos';

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
		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.reload();
		});
	}

	confirmValidateDeposit(id) {
		this.validateId = id;
		this.modalRef = this.modalService.show(this.confirmValidateModal);
	}

	validateDeposit(status: number) {
		/*this.dataService.put(endpoint('deposit_verify', { id: this.validateId }), { status })
			.subscribe((response: any) => {
				this.modalRef.hide();
				this.reloadTable();
			});*/
	}
}

