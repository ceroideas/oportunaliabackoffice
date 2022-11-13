import { Component, OnInit,TemplateRef, ViewChild } from '@angular/core';
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
  selector: 'app-cesion-users',
  templateUrl: './cesion-users.component.html',
  styleUrls: ['./cesion-users.component.scss']
})
export class CesionUsersComponent implements OnInit {

	public createdAt: string = '';
	public cesionStatus: string = '';
	public cesionStatusColor: string = '';

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals

	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private validateId: number;

	// Auction

	public cesionId: number;
	public cesion: Auction = {} as any;

  constructor(
		private router: Router,
		private route: ActivatedRoute,
		private modalService: BsModalService,
		public utils: UtilsService,
		public dataService: DataService,
		public auctionsService: AuctionsService
  ) {

  }

	ngOnInit(): void {

		this.route.params.subscribe(params => {

			if (!params['id']) { this.router.navigate(['/cesions']); }

			this.cesionId = params['id'];

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

		this.auctionsService.getAuction(this.cesionId)
		.subscribe((data: BaseResponse<Auction>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.cesion = data.response;

				this.createdAt = formatDate(this.cesion.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');

				let d1 = new Date(this.cesion.start_date);
				let d2 = new Date(this.cesion.end_date);
				let d3 = new Date();

				if (this.cesion.sold_at) {
					this.cesionStatus = 'Vendida';
					this.cesionStatusColor = 'text-green';
				} else if (d1 > d3) {
					this.cesionStatus = 'Próximamente';
					this.cesionStatusColor = 'text-purple';
				} else if (d3 > d1 && d3 < d2) {
					this.cesionStatus = 'En curso';
					this.cesionStatusColor = 'text-cyan';
				} else {
					this.cesionStatus = 'No vendida';
					this.cesionStatusColor = 'text-red';
				}
			}

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
			this.router.navigate(['/cesions']);
		});
	}

	initTable(): void {

		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('cesion_users_dt', { id: this.cesionId }),
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
					title: 'Usuario', data: 'username',
				},
				{
					title: 'Rol', data: 'role',
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
					title: 'Email', data: 'email'
				},
				{
					title: 'Teléfono', data: 'phone'
				},
				{
					title: 'Dirección',
					data: function (row) {

						let address = row.address ?? '';
						let city = row.city ?
							(row.address ? ' ' : '') + row.city : '';
						let province = row.province ?
							(row.city ? ', ' :
							 	(row.address ? ' ' : '')
						 	) + row.province : '';
						return `${address}${city}${province}`;
					}
				},
				{
					title: 'Oferta', className: 'all',
					data: function (row) {
						return row.import + " €";
					}
				},
				{
					title: 'Validar', orderable: false, data: null, className: 'all text-center not-filterable',
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
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function () {

						let render = '';

						render += `<button class="editar btn btn-table btn-navy">
							<i class="fa fa-pencil-alt"></i>
						</button>`;

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: [-1, -2], orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.validar', row).unbind('click');
				$('button.validar', row).bind('click', () => {
					this.confirmValidateOffer(data['id']);
				});

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					that.router.navigate(['/users', data['id'], 'edit']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#cesion-usuarios';

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

		let newUrl = endpoint('cesion_users_dt', { id: this.cesionId });

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

  confirmValidateOffer(id) {
		this.validateId = id;
		this.modalRef = this.modalService.show(this.confirmValidateModal, { class: 'modal-md' });
	}

	validateOffer(status: number) {

		this.auctionsService.validateOffer(this.validateId, status)
		.subscribe(data => {
			if (status == 1) {
				this.utils.showToast('La oferta de ese usuario ha sido aceptada');
			} else if (status == 2) {
				this.utils.showToast('La oferta de ese usuario ha sido rechazada');
			}
			this.reloadTable();
			this.modalRef.hide();
		});
	}
}
