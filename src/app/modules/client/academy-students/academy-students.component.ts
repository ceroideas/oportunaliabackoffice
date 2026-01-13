import { Component, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { Router } from '@angular/router';
import { formatDate } from '@angular/common';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-students',
	templateUrl: './academy-students.component.html',
	styleUrls: ['./academy-students.component.scss']
})
export class AcademyStudentsComponent implements OnInit {

	// DataTables
	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	// Modals
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private deleteId: number;

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public dataService: DataService
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

	normalizeText(text: string): string {
		return text ? text.normalize("NFD").replace(/[\u0300-\u036f]/g, "") : "";
	}

	initTable(): void {
		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('academy_students_dt'),
				type: 'get',
				headers: {
					Authorization: sessionStorage.getItem('token')
				},
				dataSrc: function (json) {
					that.utils.logResponse(json);
					// El backend devuelve paginación, extraer el array de datos
					return json['response'] && json['response']['data'] ? json['response']['data'] : json['response'] || [];
				}
			},
			columns: [
				{
					title: 'Email', data: 'email',
				},
				{
					title: 'Nombre', data: function(row){return '<span style="display:none">'+that.normalizeText(row.firstname)+'</span>'+row.firstname},
				},
				{
					title: 'Apellidos', data: function(row){return '<span style="display:none">'+that.normalizeText(row.lastname)+'</span>'+row.lastname},
				},
				{
					title: 'Usuario vinculado', 
					data: function (row) {
						return row.user ? row.user.firstname + ' ' + row.user.lastname : '-';
					}
				},
				{
					title: 'Fecha de registro',
					data: function (row) {
						return formatDate(row.academy_registered_at || row.created_at, 'dd/MM/yyyy HH:mm', 'es');
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function () {
						let render = '';
						render += `<button class="ver btn btn-table btn-navy" title="Ver detalle">
							<i class="fa fa-eye"></i>
						</button>`;
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
			columnDefs: [
				{ targets: -1, orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {
				$('button.ver', row).unbind('click');
				$('button.ver', row).bind('click', () => {
					that.router.navigate(['/academy/students', data['id']]);
				});

				$('button.editar', row).unbind('click');
				$('button.editar', row).bind('click', () => {
					that.router.navigate(['/academy/students', data['id'], 'edit']);
				});

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					that.deleteId = data['id'];
					that.modalRef = that.modalService.show(that.confirmModal, { class: 'modal-sm' });
				});
			}
		};
	}

	deleteStudent(): void {
		if (!this.deleteId) return;

		this.dataService.http.delete(endpoint('academy_students_delete', { id: this.deleteId }), { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					this.utils.showToast('Estudiante eliminado correctamente', 'success');
					this.modalRef.hide();
					this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
						dtInstance.ajax.reload();
					});
				} else {
					this.utils.showToast('Error al eliminar estudiante', 'error');
				}
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al eliminar estudiante', 'error');
			});
	}
}

