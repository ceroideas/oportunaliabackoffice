import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-student-edit',
	templateUrl: './academy-student-edit.component.html',
	styleUrls: ['./academy-student-edit.component.scss']
})
export class AcademyStudentEditComponent implements OnInit {

	public studentForm: FormGroup;
	public loading = false;
	public studentId: number;
	public isEdit = false;
	public users: any[] = [];

	constructor(
		private route: ActivatedRoute,
		private router: Router,
		private fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService
	) {
		this.studentForm = this.fb.group({
			email: ['', [Validators.required, Validators.email]],
			firstname: ['', [Validators.required]],
			lastname: ['', [Validators.required]],
			password: ['', [Validators.minLength(6)]],
			user_id: [null]
		});
	}

	ngOnInit(): void {
		this.route.params.subscribe(params => {
			this.studentId = params.id;
			if (this.studentId) {
				this.isEdit = true;
				this.loadStudent();
			}
		});
	}

	loadStudent(): void {
		this.loading = true;
		this.dataService.http.get(endpoint('academy_students_get', { id: this.studentId }), { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					const student = response.response;
					this.studentForm.patchValue({
						email: student.email,
						firstname: student.firstname,
						lastname: student.lastname,
						user_id: student.user_id
					});
					// Password no es requerido en edición, pero mantener validación de longitud si se proporciona
					this.studentForm.get('password').setValidators([Validators.minLength(6)]);
					this.studentForm.get('password').updateValueAndValidity();
				} else {
					this.utils.showToast('Error al cargar estudiante', 'error');
					this.goBack();
				}
				this.loading = false;
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al cargar estudiante', 'error');
				this.loading = false;
				this.goBack();
			});
	}

	save(): void {
		// Validación especial: password es requerido solo en creación
		if (!this.isEdit && !this.studentForm.get('password').value) {
			this.utils.showToast('La contraseña es requerida', 'error');
			return;
		}

		if (this.studentForm.invalid) {
			this.utils.showToast('Por favor, complete todos los campos requeridos', 'error');
			return;
		}

		this.loading = true;
		const formData = { ...this.studentForm.value };
		
		// Si no hay password en edición, eliminarlo
		if (this.isEdit && !formData.password) {
			delete formData.password;
		}
		
		// En creación, password es requerido
		if (!this.isEdit && formData.password && formData.password.length < 6) {
			this.utils.showToast('La contraseña debe tener al menos 6 caracteres', 'error');
			this.loading = false;
			return;
		}

		const url = this.isEdit 
			? endpoint('academy_students_edit', { id: this.studentId })
			: endpoint('academy_students_create');
		
		const method = this.isEdit ? 'post' : 'post';

		this.dataService.http[method](url, formData, { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					this.utils.showToast(this.isEdit ? 'Estudiante actualizado correctamente' : 'Estudiante creado correctamente', 'success');
					if (this.isEdit) {
						this.router.navigate(['/academy/students', this.studentId]);
					} else {
						this.router.navigate(['/academy/students']);
					}
				} else {
					this.utils.showToast(response.messages?.join(', ') || 'Error al guardar estudiante', 'error');
				}
				this.loading = false;
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al guardar estudiante', 'error');
				this.loading = false;
			});
	}

	goBack(): void {
		if (this.isEdit) {
			this.router.navigate(['/academy/students', this.studentId]);
		} else {
			this.router.navigate(['/academy/students']);
		}
	}
}

