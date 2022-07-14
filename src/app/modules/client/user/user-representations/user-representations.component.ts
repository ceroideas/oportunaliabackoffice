import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { UsersService } from 'src/app/core/services/users.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { User, Representation, RepresentationType } from 'src/app/shared/models/user.model';

@Component({
	selector: 'app-user-representations',
	templateUrl: './user-representations.component.html',
	styleUrls: ['./user-representations.component.scss']
})
export class UserRepresentationsComponent implements OnInit {

	public representationId: number;

	public createdAt: string = '';
	public userStatus: string = '';
	public userStatusColor: string = '';

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';
	private filter_type_id: any;

	// Selectors

	public types: RepresentationType[] = [];

	// Modals

	@ViewChild('representationModal') representationModal: TemplateRef<any>;
	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private validateId: number;
	private deleteId: number;

	// User

	public userId: number;
	public user: User;

	constructor(
		private router: Router,
		private route: ActivatedRoute,
		public utils: UtilsService,
		public dataService: DataService,
		private modalService: BsModalService,
		public userService: UsersService
	) {
	}

	ngOnInit(): void {

		this.dataService.getRepresentationTypes()
		.then((val: RepresentationType) => {
			this.types = val['response'];
		});

		this.route.params.subscribe(params => {

			if (!params['id']) { this.router.navigate(['/users']); }

			this.userId = params['id'];

			this.initTable();

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
				this.router.navigate(['/users', this.userId, 'edit']);
			});
		});
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
				url: endpoint('user_representations_dt', { id: this.userId }),
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
					title: 'Tipo de representación', data: 'representation_type'
				},
				{
					title: 'Alias', data: 'alias'
				},
				{
					title: 'Nombre', data: 'firstname'
				},
				{
					title: 'Apellidos', data: 'lastname'
				},
				{
					title: 'NIF/CIF', data: 'document_number'
				},
				{
					title: 'Poderes', orderable: false, data: null, className: 'all text-center',
					render: function (data, type, row, meta) {

						if (row.document?.path) {

							return `<button class="poder btn btn-table btn-navy" title="${row.document.name}">
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
					title: 'Validar poderes', orderable: false, data: null, className: 'all text-center',
					render: function (data, type, row, meta) {

						switch (row.status) {
							case 1: return `<div class="pill pill-green" title="Cambiar validación">Validados</div>`; break;
							case 2: return `<div class="pill pill-red" title="Cambiar validación">No validados</div>`; break;
							default: return `<button class="validar btn btn-table btn-navy m-0" title="Validar">
								<i class="fa fa-check"></i>
							</button>`;
						}
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all',
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
				{ targets: [-1, -2, -3], orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.poder', row).unbind('click');
				$('button.poder', row).bind('click', () => {
					window.open(data['document']['path'], '_blank').focus();
				});

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					this.openModal(data['id']);
				});

				$('.validar', row).unbind('click');
				$('.validar', row).bind('click', () => {
					this.confirmValidateRepresentation(data['id']);
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
					{ name: 'Verificados', value: 'validated' },
					{ name: 'Pendientes', value: 'pending' },
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

				let type_id = $(`<select class="text-darkgray"
						data-filter="type_id"
						style="width:12rem"
					></select>`);

				type_id.append(`<option value="" class="text-gray">
					Tipo representación
				</option>`);

				that.types.forEach(option => {
					type_id.append(`<option value="${ option.id }">
						${ option.name }
					</option>`);
				});

				$('.dataTables_filters').append(
					$('<div>', { class: 'dataTables_filter_select' })
						.append(type_id)
						.append(`<i class="icon fa fa-tag text-gray ml-2"></i>`)
				);

				$('[data-filter=type_id]').change(evt => {

					let elem = $(evt.target).find('option:selected');
					that.filter_type_id = elem.val();

					that.reloadTable();
				});

				// Column filters

				let table_id = '#usuario-representaciones';

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

		switch (this.filter_tab) {
			case 'validated':
				params.status = 1;
				break;
			case 'pending':
				params.status = 0;
				break;
		}

		if (this.filter_type_id) {
			params.representation_type_id = this.filter_type_id;
		}

		let newUrl = endpoint('user_representations_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	openModal(id?: number) {
		this.representationId = id;
		this.modalRef = this.modalService.show(this.representationModal, { class: 'modal-lg' });
	}

	confirmValidateRepresentation(id: number) {
		this.validateId = id;
		this.modalRef = this.modalService.show(this.confirmValidateModal, { class: 'modal-md' });
	}

	validateRepresentation(status: number) {

		this.userService.validateRepresentation(this.validateId, status)
		.subscribe(data => {
			if (status == 1) {
				this.utils.showToast('La representación de usuario ha sido validada');
			} else if (status == 2) {
				this.utils.showToast('La representación de usuario ha sido invalidada');
			}
			this.reloadTable();
			this.modalRef.hide();
		});
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-xs' });
	}

	deleteRepresentation() {

		this.userService.deleteRepresentation(this.deleteId)
		.subscribe(data => {
			this.utils.showToast('La representación de usuario ha sido borrada');
			this.reloadTable();
			this.modalRef.hide();
		});
	}
}
