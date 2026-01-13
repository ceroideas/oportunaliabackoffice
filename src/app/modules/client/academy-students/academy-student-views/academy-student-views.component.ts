import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { formatDate } from '@angular/common';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-student-views',
	templateUrl: './academy-student-views.component.html',
	styleUrls: ['./academy-student-views.component.scss']
})
export class AcademyStudentViewsComponent implements OnInit {

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
					return json.response?.views || [];
				}
			},
			columns: [
				{
					title: 'Curso', 
					data: function(row) {
						return row.course?.title || '-';
					}
				},
				{
					title: 'Progreso', 
					data: function(row) {
						return row.progress_percentage + '%';
					}
				},
				{
					title: 'Última posición',
					data: function(row) {
						const totalSeconds = row.last_position_seconds || 0;
						const minutes = Math.floor(totalSeconds / 60);
						const seconds = totalSeconds % 60;
						return `${minutes}:${seconds.toString().padStart(2, '0')}`;
					}
				},
				{
					title: 'Última visualización',
					data: function (row) {
						return formatDate(row.viewed_at, 'dd/MM/yyyy HH:mm', 'es');
					}
				}
			],
			order: [[3, 'desc']] // Ordenar por última visualización
		};
	}

	goBack(): void {
		this.router.navigate(['/academy/students', this.studentId]);
	}
}

