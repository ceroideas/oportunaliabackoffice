import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { AssetsService } from 'src/app/core/services/assets.service';

import { AuctionStatus } from 'src/app/shared/models/auction.model';
import { AssetCategory } from 'src/app/shared/models/asset.model';

@Component({
	selector: 'app-assets',
	templateUrl: './assets.component.html',
	styleUrls: ['./assets.component.scss']
})
export class AssetsComponent implements OnInit {

	public assetId: number;

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_category_id: any;

	// Selectors

	public categories: AssetCategory[] = [];

	// Modals

	@ViewChild('assetModal') assetModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private deleteId: number;

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public dataService: DataService,
		public assetsService: AssetsService
	) {
	}

	ngOnInit(): void {

		this.dataService.getAssetCategories()
		.then((val: AssetCategory) => {
			this.categories = val['response'];
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
				url: endpoint('assets_dt'),
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
					title: 'Referencia', data: 'id'
				},
				{
					title: 'Nombre activo', data: 'name'
				},
				{
					title: 'Categoría', data: 'active_category'
				},
				{
					title: 'Localidad', data: 'city'
				},
				{
					title: 'Provincia', data: 'province'
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function (data, type, row, meta) {

						let render = '';

						render += `<button class="editar btn btn-table btn-navy" title="Editar">
							<i class="fa fa-pencil-alt"></i>
						</button>`;

						render += `<button class="borrar btn btn-table btn-red" title="Borrar">
							<i class="fa fa-trash"></i>
						</button>`;

						render += `<button class="duplicar btn btn-table btn-navy" title="Duplicar">
							<i class="fa fa-copy"></i>
						</button>`;

						if (row.auction_id != null)
						{
							if (row.auction_type_id == 1) {

								render += `<button class="ir-subasta btn btn-table btn-navy" title="Ver subasta">
									<i class="fa fa-gavel"></i>
								</button>`;

							} else if (row.auction_type_id == 2){

								render += `<button class="ir-venta btn btn-table btn-navy" title="Ver venta directa">
									<i class="fa fa-money-bill-wave-alt"></i>
								</button>`;
							} else {
                render += `<button class="ir-cesion btn btn-table btn-navy" title="Ver cesion de remate">
                <i class="fa fa-share"></i>
              </button>`;
              }
						}
						else
						{
							render += `<button class="subasta btn btn-table btn-cyan" title="Crear subasta">
								<i class="fa fa-gavel"></i>
							</button>`;

							render += `<button class="venta btn btn-table btn-green" title="Crear venta directa">
								<i class="fa fa-money-bill-wave-alt"></i>
							</button>`;

              render += `<button class="cesion btn btn-table btn-green" title="Crear cesion de remate">
              <i class="fa fa-share"></i>
            </button>`;
						}

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: -1, orderable: false },
			],
			order: [[0, 'desc']],
			rowCallback: (row: Node, data: any | Object, index: number) => {

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					this.openModal(data['id']);
				});

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					this.confirmDelete(data['id']);
				});

				$('button.duplicar', row).unbind('click');
				$('button.duplicar', row).bind('click', () => {
					this.duplicateAsset(data['id']);
				});

				$('button.subasta', row).unbind('click');
				$('button.subasta', row).bind('click', () => {
					that.router.navigate(['/auctions', 'create'],
						{ queryParams: { active_id: data['id'] }}
					);
				});

				$('button.venta', row).unbind('click');
				$('button.venta', row).bind('click', () => {
					that.router.navigate(['/direct-sellings', 'create'],
						{ queryParams: { active_id: data['id'] }}
					);
				});

				$('button.cesion', row).unbind('click');
				$('button.cesion', row).bind('click', () => {
					that.router.navigate(['/cesions', 'create'],
						{ queryParams: { active_id: data['id'] }}
					);
				});

				$('button.ir-subasta', row).unbind('click');
				$('button.ir-subasta', row).bind('click', () => {
					that.router.navigate(['/auctions', data.auction_id, 'edit']);
				});

				$('button.ir-venta', row).unbind('click');
				$('button.ir-venta', row).bind('click', () => {
					that.router.navigate(['/direct-sellings', data.auction_id, 'edit']);
				});

        $('button.ir-cesion', row).unbind('click');
				$('button.ir-cesion', row).bind('click', () => {
					that.router.navigate(['/cesions', data.auction_id, 'edit']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Select filters

				let category_id = $(`<select class="text-darkgray"
						data-filter="category_id"
						style="width:12rem"
					></select>`);

				category_id.append(`<option value="" class="text-gray">
					Categoría
				</option>`);

				that.categories.forEach(option => {
					category_id.append(`<option value="${ option.id }">
						${ option.name }
					</option>`);
				});

				$('.dataTables_filters').append(
					$('<div>', { class: 'dataTables_filter_select' })
						.append(category_id)
						.append(`<i class="icon fa fa-tag text-gray ml-2"></i>`)
				);

				$('[data-filter=category_id]').change(evt => {

					let elem = $(evt.target).find('option:selected');
					that.filter_category_id = elem.val();

					that.reloadTable();
				});

				// Column filters

				let table_id = '#activos';

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

		if (this.filter_category_id) {
			params.active_category_id = this.filter_category_id;
		}

		let newUrl = endpoint('assets_dt', params);

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	openModal(id?: number) {
		this.assetId = id;
		this.modalRef = this.modalService.show(this.assetModal, { class: 'modal-lg' });
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-xs' });
	}

	deleteAsset() {

		this.assetsService.deleteAsset(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('El activo ha sido borrado');
			this.modalRef.hide();
		});
	}

	duplicateAsset(id: number){

		this.assetsService.duplicateAsset(id)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('El activo ha sido duplicado');
		});

	}

	download(type) {

		this.utils.showToast(`Se está exportando en formato ${type}`);

		this.assetsService.assetsExport(type)
		.subscribe(data => {
			this.utils.downloadFile(data, type, 'Activos');
		});
	}
}
