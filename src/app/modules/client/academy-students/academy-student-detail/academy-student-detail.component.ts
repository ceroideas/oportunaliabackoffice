import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { formatDate } from '@angular/common';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-student-detail',
	templateUrl: './academy-student-detail.component.html',
	styleUrls: ['./academy-student-detail.component.scss']
})
export class AcademyStudentDetailComponent implements OnInit {

	public student: any = null;
	public loading = true;
	public studentId: number;

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
		});
	}

	loadStudent(): void {
		this.loading = true;
		this.dataService.http.get(endpoint('academy_students_get', { id: this.studentId }), { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					this.student = response.response;
				} else {
					this.utils.showToast('Error al cargar estudiante', 'error');
					this.router.navigate(['/academy/students']);
				}
				this.loading = false;
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al cargar estudiante', 'error');
				this.loading = false;
				this.router.navigate(['/academy/students']);
			});
	}

	formatDate(date: string): string {
		if (!date) return '-';
		return formatDate(date, 'dd/MM/yyyy HH:mm', 'es');
	}

	goBack(): void {
		this.router.navigate(['/academy/students']);
	}

	goToEdit(): void {
		this.router.navigate(['/academy/students', this.studentId, 'edit']);
	}

	goToCourses(): void {
		this.router.navigate(['/academy/students', this.studentId, 'courses']);
	}

	goToViews(): void {
		this.router.navigate(['/academy/students', this.studentId, 'views']);
	}
}

