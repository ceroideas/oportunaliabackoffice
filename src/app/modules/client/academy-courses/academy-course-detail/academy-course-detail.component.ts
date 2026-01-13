import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { formatDate } from '@angular/common';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint, environment } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-course-detail',
	templateUrl: './academy-course-detail.component.html',
	styleUrls: ['./academy-course-detail.component.scss']
})
export class AcademyCourseDetailComponent implements OnInit {

	public course: any = null;
	public loading = true;
	public courseId: number;

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();

	constructor(
		private route: ActivatedRoute,
		private router: Router,
		public utils: UtilsService,
		public dataService: DataService
	) {
	}

	ngOnInit(): void {
		this.route.params.subscribe(params => {
			this.courseId = params.id;
			this.loadCourse();
			this.initViewsTable();
		});
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.dtTrigger.next();
	}

	loadCourse(): void {
		this.loading = true;
		this.dataService.http.get(endpoint('academy_courses_get', { id: this.courseId }), { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					this.course = response.response;
					// Formatear materiales si existen
					if (this.course.materials && this.course.materials.length > 0) {
						this.course.materials = this.course.materials.map(m => ({
							...m,
							formatted_file_size: this.formatFileSize(m.file_size)
						}));
						// Ordenar por ID (orden de creación, más antiguo primero)
						this.course.materials.sort((a, b) => (a.id || 0) - (b.id || 0));
					}
				} else {
					this.utils.showToast('Error al cargar curso', 'error');
					this.router.navigate(['/academy/courses']);
				}
				this.loading = false;
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al cargar curso', 'error');
				this.loading = false;
				this.router.navigate(['/academy/courses']);
			});
	}

	initViewsTable(): void {
		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('academy_courses_get', { id: this.courseId }),
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
					title: 'Progreso', 
					data: function(row) {
						return row.progress_percentage + '%';
					}
				},
				{
					title: 'Última visualización',
					data: function (row) {
						return formatDate(row.viewed_at, 'dd/MM/yyyy HH:mm', 'es');
					}
				}
			],
			order: [[3, 'desc']]
		};
	}

	formatDate(date: string): string {
		if (!date) return '-';
		return formatDate(date, 'dd/MM/yyyy HH:mm', 'es');
	}

	goBack(): void {
		this.router.navigate(['/academy/courses']);
	}

	goToEdit(): void {
		this.router.navigate(['/academy/courses', this.courseId, 'edit']);
	}

	/**
	 * Obtener URL completa de la imagen/video usando apiBase
	 */
	getFileUrl(path: string): string {
		if (!path) return '';
		// Extraer la URL base (sin /api)
		const apiBase = environment.url.replace('/api', '');
		return `${apiBase}/${path}`;
	}

	/**
	 * Obtener URL completa de un material
	 */
	getMaterialUrl(material: any): string {
		if (!material || !material.file_path) return '';
		return this.getFileUrl(material.file_path);
	}

	/**
	 * Formatear tamaño de archivo
	 */
	formatFileSize(bytes: number): string {
		if (!bytes || bytes === 0) return '0 B';
		const k = 1024;
		const sizes = ['B', 'KB', 'MB', 'GB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
	}
}

