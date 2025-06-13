import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { UsersService } from 'src/app/core/services/users.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Role } from 'src/app/shared/models/data.model';
import { User } from 'src/app/shared/models/user.model';

@Component({
	selector: 'app-user',
	templateUrl: './user.component.html',
	styleUrls: ['./user.component.scss']
})
export class UserComponent implements OnInit {

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';
	private filter_role_id: any;

	// Selectors

	public roles: Role[] = [];

	// Modals

	@ViewChild('userModal') userModal: TemplateRef<any>;
	@ViewChild('confirmConfirmModal') confirmConfirmModal: TemplateRef<any>;
	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	public user: User;
	private confirmId: number;
	private validateId: number;
	private deleteId: number;

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public dataService: DataService,
		public userService: UsersService
	) {
	}

	ngOnInit(): void {

		this.dataService.getRoles().then((val: Role) => {
			this.roles = val['response'];
		});

		this.initTable();
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
				url: endpoint('users_dt'),
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
					title: 'Email',
					data: function (row) {

						if (row.confirmed == 1) { return row.email; }
						else {
							return `<div class="d-flex flex-column">
								<div>${ row.email }</div>
								<div class="text-red">
									<i class="fa fa-circle small mr-1"></i>
									<span>Sin verificar</span>
								</div>
							</div>`;
						}
					}
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
					title: 'Fecha de registro',
					data: function (row) {
						return formatDate(row.created_at, 'dd/MM/yyyy HH:mm', 'es');
					}
				},
				{
					title: 'Confirmado', className: 'all text-center not-filterable', orderable: false,
					data: function (row, data, type) {
						if (row.confirmed == 1) {
							return `<div class="flag-check checked"></div>`;
						} else {
							return `<div class="confirmar flag-check pointer" title="Confirmar usuario"></div>`;
						}
					}
				},
				{
					title: 'Documento 1', orderable: false, data: null, className: 'all text-center not-filterable',
					render: function (data, type, row, meta) {

						if (row.document?.path) {

							return `<button class="documento1 btn btn-table btn-navy m-0" title="${row.document.name}">
								<i class="fa fa-file-download"></i>
							</button>`;

						} else {

							return `<div class="text-red">
								<i class="fa fa-circle small mr-2"></i>
								<span>Pendiente</span>
							</div>`;
						}
					}
				},
				{
					title: 'Documento 2', orderable: false, data: null, className: 'all text-center not-filterable',
					render: function (data, type, row, meta) {

						if (row.document_two?.path) {

							return `<button class="documento2 btn btn-table btn-navy m-0" title="${row.document_two.name}">
								<i class="fa fa-file-download"></i>
							</button>`;

						} else {

							return `<div class="text-red">
								<i class="fa fa-circle small mr-2"></i>
								<span>Pendiente</span>
							</div>`;
						}
					}
				},
				{
					title: 'Validar', className: 'all text-center not-filterable', orderable: false,
					data: function (row, data, type) {

						switch (row.status) {
							case 1: return `<button class="validar btn pill pill-green" title="Cambiar validación">Validado</button>`; break;
							case 2: return `<button class="validar btn pill pill-red" title="Cambiar validación">No validado</button>`; break;
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

						render += `<button class="editar btn btn-table btn-navy" title="Editar">
							<i class="fa fa-pencil-alt"></i>
						</button>`;

						render += `<button class="borrar btn btn-table btn-red" title="Borrar">
							<i class="fa fa-trash"></i>
						</button>`;

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: -1, orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.documento1', row).unbind('click');
				$('button.documento1', row).bind('click', () => {
					window.open(data['document']['path'], '_blank').focus();
				});

				$('button.documento2', row).unbind('click');
				$('button.documento2', row).bind('click', () => {
					window.open(data['document_two']['path'], '_blank').focus();
				});

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					that.router.navigate(['/users', data['id'], 'edit']);
				});

				$('.confirmar', row).unbind('click');
				$('.confirmar', row).bind('click', () => {
					this.confirmConfirmUser(data['id']);
				});

				$('.validar', row).unbind('click');
				$('.validar', row).bind('click', () => {
					this.confirmValidateUser(data['id']);
				});

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					this.confirmDelete(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Tab filters

				let filters = [
					{ name: 'Todos', value: 'all' },
					{ name: 'Registro verificado', value: 'validated' },
					{ name: 'Pendiente de verificar', value: 'pending' },
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

				let role_id = $(`<select class="text-darkgray"
						data-filter="role_id"
						style="width:12rem"
					></select>`);

				role_id.append(`<option value="" class="text-gray">
					Rol de usuario
				</option>`);

				that.roles.forEach(option => {
					role_id.append(`<option value="${ option.id }">
						${ option.description }
					</option>`);
				});

				$('.dataTables_filters').append(
					$('<div>', { class: 'dataTables_filter_select' })
						.append(role_id)
						.append(`<i class="icon fa fa-user-tag text-gray ml-2"></i>`)
				);

				$('[data-filter=role_id]').change(evt => {

					let elem = $(evt.target).find('option:selected');
					that.filter_role_id = elem.val();

					that.reloadTable();
				});

				// Column filters

				let table_id = '#usuarios';

				$('thead tr th:nth-child(10)', table_id).css({ 'max-width': '100px' });
				$('thead tr th:nth-child(11)', table_id).css({ 'max-width': '100px' });
				$('thead tr th:nth-child(12)', table_id).css({ 'max-width': '100px' });

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

		let params: any = {};

		switch (this.filter_tab) {
			case 'validated':
				params.confirmed = 1;
				break;
			case 'pending':
				params.confirmed = 0;
				break;
		}

		if (this.filter_role_id) {
			params.role_id = this.filter_role_id;
		}

		let newUrl = endpoint('users_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	edituser(id) {

		this.userService.getUser(id)
		.subscribe(val => {
			this.user = val['response'][0];
		});

		this.modalRef = this.modalService.show(this.userModal, { class: 'modal-lg' });
	}

	confirmConfirmUser(id) {
		this.confirmId = id;
		this.modalRef = this.modalService.show(this.confirmConfirmModal, { class: 'modal-xs' });
	}

	confirmUser() {

		this.userService.confirmUser(this.confirmId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('El usuario ha sido confirmado');
			this.modalRef.hide();
		});
	}

	confirmValidateUser(id) {
		this.validateId = id;
		this.modalRef = this.modalService.show(this.confirmValidateModal, { class: 'modal-md' });
	}

	validateUser(status: number) {

		this.userService.validateUser(this.validateId, status)
		.subscribe(data => {
			if (status == 1) {
				this.utils.showToast('La documentación del usuario ha sido validada');
			} else if (status == 2) {
				this.utils.showToast('La documentación del usuario ha sido invalidada');
			}
			this.reloadTable();
			this.modalRef.hide();
		});
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteUser() {

		this.userService.deleteUser(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('El usuario ha sido borrado');
			this.modalRef.hide();
		});
	}

	download(type: string) {

		if (type == 'interests') {
			this.utils.showToast(`Se están exportando los usuarios con los intereses`);

			this.userService.usersExport(type)
			.subscribe(data => {
				this.utils.downloadFile(data, type, 'Usuarios');
			});
			return;
		}

		this.utils.showToast(`Se está exportando en formato ${type}`);

		this.userService.usersExport(type)
		.subscribe(data => {
			this.utils.downloadFile(data, type, 'Usuarios');
		});
	}
}
