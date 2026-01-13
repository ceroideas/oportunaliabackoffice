import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { formatDate } from '@angular/common';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-payments',
	templateUrl: './academy-payments.component.html',
	styleUrls: ['./academy-payments.component.scss']
})
export class AcademyPaymentsComponent implements OnInit {

	// DataTables
	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	constructor(
		private router: Router,
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

	initTable(): void {
		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('academy_payments_dt'),
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
					title: 'ID de Pago Stripe', 
					data: 'stripe_payment_intent_id',
				},
				{
					title: 'Estudiante', 
					data: function(row) {
						return row.student ? row.student.firstname + ' ' + row.student.lastname : '-';
					}
				},
				{
					title: 'Email', 
					data: function(row) {
						return row.student?.email || '-';
					}
				},
				{
					title: 'Curso', 
					data: function(row) {
						return row.course?.title || '-';
					}
				},
				{
					title: 'Cantidad', 
					data: function(row) {
						return row.amount + ' €';
					}
				},
				{
					title: 'Estado', 
					data: function(row) {
						switch(row.status) {
							case 'succeeded': return '<span class="badge badge-success">Completado</span>';
							case 'pending': return '<span class="badge badge-warning">Pendiente</span>';
							case 'failed': return '<span class="badge badge-danger">Fallido</span>';
							case 'refunded': return '<span class="badge badge-secondary">Reembolsado</span>';
							default: return '-';
						}
					}
				},
				{
					title: 'Fecha de pago',
					data: function (row) {
						return row.payment_date ? formatDate(row.payment_date, 'dd/MM/yyyy HH:mm', 'es') : '-';
					}
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function () {
						return `<button class="ver btn btn-table btn-navy" title="Ver detalle">
							<i class="fa fa-eye"></i>
						</button>`;
					}
				}
			],
			columnDefs: [
				{ targets: -1, orderable: false },
			],
			order: [[6, 'desc']], // Ordenar por fecha de pago
			rowCallback: (row: Node, data: any[] | Object, index: number) => {
				$('button.ver', row).unbind('click');
				$('button.ver', row).bind('click', () => {
					that.router.navigate(['/academy/payments', data['id']]);
				});
			}
		};
	}
}

