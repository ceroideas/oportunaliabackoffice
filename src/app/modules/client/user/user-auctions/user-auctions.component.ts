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
	selector: 'app-user-auctions',
	templateUrl: './user-auctions.component.html',
	styleUrls: ['./user-auctions.component.scss']
})
export class UserAuctionsComponent implements OnInit {

	public createdAt: string = '';
	public userStatus: string = '';
	public userStatusColor: string = '';

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals

	@ViewChild('historyModal') historyModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private auctionId: number;
	private deleteId: number;

	// User

	public userId: number;
	public user: User;

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
				url: endpoint('user_auctions_dt', { id: this.userId }),
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
					render: function () {

						let render = '';

						render += `<button class="editar btn btn-table btn-navy" title="Editar">
							<i class="fa fa-pencil-alt"></i>
						</button>`;

						render += `<button class="historial btn btn-table btn-navy" title="Ver historial de pujas">
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
					that.router.navigate(['/auctions', data['auction_id'], 'edit']);
				});

				$('button.historial', row).unbind('click');
				$('button.historial', row).bind('click', () => {
					this.showHistory(data['auction_id']);
				});

				// $('button.borrar', row).unbind('click');
				// $('button.borrar', row).bind('click', () => {
				// 	this.confirmDelete(data['id']);
				// });

				return row;
			},
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#usuario-subastas';

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

		let newUrl = endpoint('user_auctions_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	showHistory(id) {
		this.auctionId = id;
		this.modalRef = this.modalService.show(this.historyModal, { class: 'modal-lg' });
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
}
