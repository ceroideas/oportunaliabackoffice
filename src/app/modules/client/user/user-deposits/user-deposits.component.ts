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

@Component({
	selector: 'app-user-deposits',
	templateUrl: './user-deposits.component.html',
	styleUrls: ['./user-deposits.component.scss']
})
export class UserDepositsComponent implements OnInit {

	public createdAt: string = '';
	public userStatus: string = '';
	public userStatusColor: string = '';

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals

	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private validateId: number;

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
				url: endpoint('user_deposits_dt', { id: this.userId }),
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
					title: 'Referencia subasta', data: 'reference'
				},
				{
					title: 'Fecha',
					data: function (row) {
						return formatDate(row.created_at, 'dd/MM/yyyy HH:mm', 'es');
					}
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

				let table_id = '#usuario-depositos';

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

		let newUrl = endpoint('user_deposits_dt', { id: this.userId });

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
