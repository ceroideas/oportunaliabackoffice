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
	selector: 'app-auction-users',
	templateUrl: './auction-users.component.html',
	styleUrls: ['./auction-users.component.scss']
})
export class AuctionUsersComponent implements OnInit {

	public createdAt: string = '';
	public auctionStatus: string = '';
	public auctionStatusColor: string = '';

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

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

        let tmp_date = new Date(this.auction.created_at);
				tmp_date.setHours(tmp_date.getHours() + 1)
				this.createdAt = formatDate(tmp_date, 'dd/MM/yyyy HH:mm:ss', 'es');
				//this.createdAt = formatDate(this.auction.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');

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
				url: endpoint('auction_users_dt', { id: this.auctionId }),
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
					title: 'Puja del usuario', data: null, className: 'all',
					render: function (data, type, row, meta) {

						let iconColor, textColor, text;

						if (row.is_best_bid == 1) {
							iconColor = 'text-cyan'; textColor = 'text-black'; text = 'Puja ganadora';
						} else {
							iconColor = 'text-red'; textColor = 'text-gray'; text = 'Puja superada';
						}

						return `<div class="d-flex flex-column">
							<div>${ row.import } €</div>
							<div>
								<i class="fa fa-gavel small ${iconColor}"></i>
								<span class="${textColor}">${text}</span>
							</div>
						</div>`;
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function () {

						let render = '';

						render += `<button class="editar btn btn-table btn-navy" title="Ir a datos de usuario">
							<i class="fa fa-pencil-alt"></i>
						</button>`;

						return render;
					}
				},
				{
					data: 'import', visible: false,
				}
			],
			columnDefs:[
				{ targets: -1, orderable: false },
				{ targets: 8, type: "num", orderData: 10 }, { targets: 10, visible: false },
			],
			order: [[ 8, "desc" ]],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					that.router.navigate(['/users', data['user_id'], 'edit']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#subasta-usuarios';

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

		let newUrl = endpoint('auction_users_dt', { id: this.auctionId });

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}
}
