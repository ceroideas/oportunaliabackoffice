import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { UsersService } from 'src/app/core/services/users.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { User } from 'src/app/shared/models/user.model';
import { AuctionStatus } from 'src/app/shared/models/auction.model';

@Component({
  selector: 'app-user-cesions',
  templateUrl: './user-cesions.component.html',
  styleUrls: ['./user-cesions.component.scss']
})
export class UserCesionsComponent implements OnInit {

  	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_start_date: any;
	private filter_end_date: any;

	// Modals

	@ViewChild('historyModal') historyModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private cesionId: number;
	private deleteId: number;

	// User

	public userId: number;
	public user: User;
	public createdAt: string = '';
	public userStatus: string = '';
	public userStatusColor: string = '';

  constructor(
		private router: Router,
		private route: ActivatedRoute,
		private modalService: BsModalService,
		public utils: UtilsService,
		public userService: UsersService,
		public auctionsService: AuctionsService
  ) {
   }

   ngOnInit(): void {

		this.route.params.subscribe(params => {

			if (!params['id']) { this.router.navigate(['/users']); }

			this.userId = params['id'];

			this.initTable();

			this.getUser();
		});
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.dtTrigger.next();
	}

  getUser() {

		this.userService.getUser(this.userId)
		.subscribe((data: BaseResponse<User>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.user = data.response;

				this.createdAt = formatDate(this.user.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');

				let styles = this.userService.userStatus(this.user);
				this.userStatus = styles.text;
				this.userStatusColor = 'text-'+styles.color;
			}

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
			this.router.navigate(['/users']);
		});
	}

	initTable(): void {

		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('user_cesions_dt', { id: this.userId }),
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
					title: 'Precio de venta',
					data: function (row) {
						return row.start_price + " €";
					}
				},
				{
					title: 'Valor de tasación',
					data: function (row) {
						return row.appraisal_value + " €";
					}
				},
				{
					title: 'Mejor oferta', className: 'all',
					data: function (row) {
						return row.max_offer ? row.max_offer + " €" : "";
					}
				},
				{
					title: 'Oferta usuario', className: 'all',
					data: function (row) {
						return row.import + " €";
					}
				},
				{
					title: 'Nº ofertas', data: 'offers'
				},
				{
					title: 'Estado', data: null, className: 'all text-center',
					render: function (data, type, row, meta) {

						switch (row.status) {
							case 1: return `<div class="pill pill-green">Aceptada</div>`; break;
							case 2: return `<div class="pill pill-red">Rechazada</div>`; break;
							default: return `<div class="pill pill-gray">Sin revisar</div>`;
						}
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function () {

						let render = '';

						render += `<button class="editar btn btn-table btn-navy" title="Editar">
							<i class="fa fa-pencil-alt"></i>
						</button>`;

						render += `<button class="historial btn btn-table btn-navy" title="Ver historial de ofertas">
							<i class="fa fa-history"></i>
						</button>`;

						// render += `<button class="borrar btn btn-table btn-red" title="Borrar">
						// 	<i class="fa fa-trash"></i>
						// </button>`;

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
					that.router.navigate(['/cesions', data['reference'], 'edit']);
				});

				$('button.historial', row).unbind('click');
				$('button.historial', row).bind('click', () => {
					this.showHistory(data['reference']);
				});

				// $('button.borrar', row).unbind('click');
				// $('button.borrar', row).bind('click', () => {
				// 	this.confirmDelete(data['reference']);
				// });

				return row;
			},
			initComplete: function(settings, json) {

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

				let table_id = '#usuario-cesiones';

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

		let params: any = { id: this.userId };

		if (this.filter_start_date) {
			params.start_date = this.filter_start_date;
		}

		if (this.filter_end_date) {
			params.end_date = this.filter_end_date;
		}

		let newUrl = endpoint('user_cesions_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	showHistory(id) {
		this.cesionId = id;
		this.modalRef = this.modalService.show(this.historyModal, { class: 'modal-lg' });
	}

  // confirmDelete(id: number) {
	// 	this.deleteId = id;
	// 	this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	// }

	// deleteDirectSelling() {

	// 	this.auctionsService.deleteDirectSelling(this.deleteId)
	// 	.subscribe(data => {
	// 		this.reloadTable();
	// 		this.utils.showToast('La venta directa ha sido borrada');
	// 		this.modalRef.hide();
	// 	});
	// }
}
