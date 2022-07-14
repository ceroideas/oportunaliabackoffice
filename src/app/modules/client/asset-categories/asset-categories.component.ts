import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { formatDate } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { AssetsService } from 'src/app/core/services/assets.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { AssetCategory } from 'src/app/shared/models/asset.model';

import { formErrors } from 'src/app/shared/validators';

@Component({
	selector: 'app-asset-categories',
	templateUrl: './asset-categories.component.html',
	styleUrls: ['./asset-categories.component.scss']
})
export class AssetCategoriesComponent implements OnInit {

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';
	private filter_type_id: any;

	// Modals

	@ViewChild('assetCategoryModal') assetCategoryModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	public modalTitle = '';
	private deleteId: number;

	// Modal forms

	public assetCategory: AssetCategory = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }
	public imagePath: string = '';

	constructor(
		private router: Router,
		private route: ActivatedRoute,
		public fb: FormBuilder,
		public utils: UtilsService,
		private modalService: BsModalService,
		public assetsService: AssetsService
	) {
	}

	ngOnInit(): void {
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
				url: endpoint('asset_categories_dt'),
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
					title: 'Nombre', data: 'name'
				},
				{
					title: 'Descripción', data: 'description'
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

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					this.openModal(data['id']);
				});

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					this.confirmDelete(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

				// Column filters

				let table_id = '#categorias-activo';

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

		let newUrl = endpoint('asset_categories_dt');

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			id: [''],
			name: ['', [Validators.required]],
			description: ['', [Validators.required]],
			image: ['', [Validators.required]],
		});
	}

	uploadImage(event: any) {

		if (event.target.files && event.target.files[0]) {
			const reader = new FileReader();
			reader.onload = (evt: any) => {
				this.form.get('image').setValue(event.target.files[0]);
				this.imagePath = evt.target.result;
			};
			reader.readAsDataURL(event.target.files[0]);
		}
	}

	openModal(id?: number) {

		this.imagePath = '';

		this.setFormFields();

		if (id) {

			this.modalTitle = 'Editar Categoría de Activo';

			this.assetsService.getAssetCategory(id)
			.subscribe((data: BaseResponse<AssetCategory>) => {
				this.assetCategory = data.response;
				this.imagePath = this.assetCategory.image;
				this.form.patchValue(this.assetCategory);
			});

		} else {

			this.modalTitle = 'Nueva Categoría de Activo';
		}

		this.modalRef = this.modalService.show(this.assetCategoryModal, { class: 'modal-lg' });
	}

	saveAssetCategory() {

		this.submitted = true;
		let assetCategoryId = this.form.get('id').value;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.assetCategory);

			const data = new FormData();

			data.append('name', this.form.get('name').value);
			data.append('description', this.form.get('description').value);
			data.append('image', this.form.get('image').value);

			if (!assetCategoryId) {

				this.assetsService.saveAssetCategory(data)
				.subscribe(data => {
					this.utils.showToast('Creado correctamente');
					this.reloadTable();
					this.modalRef.hide();
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});

			} else {

				data.append('id', assetCategoryId);

				this.assetsService.editAssetCategory(data, assetCategoryId)
				.subscribe(data => {
					this.utils.showToast('Editado correctamente');
					this.reloadTable();
					this.modalRef.hide();
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});
			}

		} else {
			this.utils.showToast('Formulario incorrecto', 'error');
		}
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteAssetCategory() {

		this.assetsService.deleteAssetCategory(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('La categoría de activo ha sido borrada');
			this.modalRef.hide();
		});
	}

	download(type) {

		this.utils.showToast(`Se está exportando en formato ${type}`);

		this.assetsService.assetCategoriesExport(type)
		.subscribe(data => {
			this.utils.downloadFile(data, type, 'Categorías de activos');
		});
	}
}
