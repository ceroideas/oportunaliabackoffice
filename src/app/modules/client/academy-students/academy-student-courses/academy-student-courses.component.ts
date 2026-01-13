import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { formatDate } from '@angular/common';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-student-courses',
	templateUrl: './academy-student-courses.component.html',
	styleUrls: ['./academy-student-courses.component.scss']
})
export class AcademyStudentCoursesComponent implements OnInit {

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	public studentId: number;
	public studentName: string = '';

	constructor(
		private route: ActivatedRoute,
		private router: Router,
		public utils: UtilsService,
		public dataService: DataService
	) {
	}

	ngOnInit(): void {
		this.route.params.subscribe(params => {
			this.studentId = params.id;
			this.loadStudent();
			this.initTable();
		});
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.dtTrigger.next();
	}

	loadStudent(): void {
		this.dataService.http.get(endpoint('academy_students_get', { id: this.studentId }), { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				if (response.code === 200) {
					this.studentName = response.response.firstname + ' ' + response.response.lastname;
				}
			});
	}

	initTable(): void {
		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('academy_students_get', { id: this.studentId }),
				type: 'get',
				headers: {
					Authorization: sessionStorage.getItem('token')
				},
				dataSrc: function (json) {
					that.utils.logResponse(json);
					return json.response?.courses || [];
				}
			},
			columns: [
				{
					title: 'Título', data: 'title',
				},
				{
					title: 'Precio', 
					data: function(row) {
						return row.is_free ? 'Gratis' : row.price + ' €';
					}
				},
				{
					title: 'Estado de pago', 
					data: function(row) {
						const status = row.pivot?.payment_status;
						switch(status) {
							case 'paid': return '<span class="badge badge-success">Pagado</span>';
							case 'pending': return '<span class="badge badge-warning">Pendiente</span>';
							case 'failed': return '<span class="badge badge-danger">Fallido</span>';
							case 'refunded': return '<span class="badge badge-secondary">Reembolsado</span>';
							default: return '-';
						}
					}
				},
				{
					title: 'Fecha de compra',
					data: function (row) {
						return row.pivot?.purchased_at ? formatDate(row.pivot.purchased_at, 'dd/MM/yyyy HH:mm', 'es') : '-';
					}
				}
			]
		};
	}

	goBack(): void {
		this.router.navigate(['/academy/students', this.studentId]);
	}
}

