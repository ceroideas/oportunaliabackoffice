import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators, FormArray } from '@angular/forms';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import { endpoint, environment } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-course-edit',
	templateUrl: './academy-course-edit.component.html',
	styleUrls: ['./academy-course-edit.component.scss']
})
export class AcademyCourseEditComponent implements OnInit {

	public courseForm: FormGroup;
	public loading = false;
	public courseId: number;
	public isEdit = false;
	public editor = ClassicEditor;
	public ckOptions = null;

	public thumbnailFile: File | null = null;
	public thumbnailPreview: string | null = null;
	public videoFile: File | null = null;
	public videoPreview: string | null = null;

	public materials: any[] = [];
	public pendingMaterials: File[] = []; // Materiales pendientes de subir durante la creación

	private readonly MAX_THUMBNAIL_SIZE = 2048000; // 2MB
	private readonly MAX_VIDEO_SIZE = 52428800; // 50MB
	private readonly MAX_MATERIAL_SIZE = 52428800; // 50MB

	constructor(
		private route: ActivatedRoute,
		private router: Router,
		private fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService
	) {
		this.ckOptions = this.utils.ckOptions;
		this.courseForm = this.fb.group({
			title: ['', [Validators.required]],
			description: [''],
			thumbnail: [null],
			video: [null],
			video_url: [''],
			price: [null],
			is_free: [false],
			order: [0],
			is_active: [true],
			tags: this.fb.array([])
		});
	}

	ngOnInit(): void {
		this.route.params.subscribe(params => {
			this.courseId = params.id;
			if (this.courseId) {
				this.isEdit = true;
				this.loadCourse();
			}
		});
	}

	get tagsFormArray() {
		return this.courseForm.get('tags') as FormArray;
	}

	addTag(value: string = '') {
		this.tagsFormArray.push(this.fb.control(value));
	}

	removeTag(index: number) {
		this.tagsFormArray.removeAt(index);
	}

	loadCourse(): void {
		this.loading = true;
		this.dataService.http.get(endpoint('academy_courses_get', { id: this.courseId }), { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					const course = response.response;
					
					// Limpiar tags existentes antes de cargar nuevos
					while (this.tagsFormArray.length !== 0) {
						this.tagsFormArray.removeAt(0);
					}
					
					// Cargar tags
					if (course.tags && course.tags.length > 0) {
						course.tags.forEach(tag => {
							this.addTag(tag.tag_name);
						});
					}

					// Preview de imágenes existentes - construir URL completa
					if (course.thumbnail_path) {
						this.thumbnailPreview = this.getFileUrl(course.thumbnail_path);
					}
					if (course.video_path) {
						this.videoPreview = this.getFileUrl(course.video_path);
					}

					// Cargar materiales
					if (course.materials && course.materials.length > 0) {
						this.materials = course.materials.map(m => ({
							...m,
							formatted_file_size: this.formatFileSize(m.file_size)
						}));
						// Ordenar por ID (orden de creación, más antiguo primero)
						this.materials.sort((a, b) => (a.id || 0) - (b.id || 0));
					} else {
						this.materials = [];
					}

					this.courseForm.patchValue({
						title: course.title,
						description: course.description,
						video_url: course.video_url,
						price: course.price,
						is_free: course.is_free,
						order: course.order,
						is_active: course.is_active
					});
				} else {
					this.utils.showToast('Error al cargar curso', 'error');
					this.goBack();
				}
				this.loading = false;
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al cargar curso', 'error');
				this.loading = false;
				this.goBack();
			});
	}

	onThumbnailSelected(event: any): void {
		const file = event.target.files[0];
		if (file) {
			if (file.size > this.MAX_THUMBNAIL_SIZE) {
				this.utils.showToast('El archivo de miniatura no puede superar los 2MB', 'error');
				return;
			}
			this.thumbnailFile = file;
			
			// Preview
			const reader = new FileReader();
			reader.onload = (e: any) => {
				this.thumbnailPreview = e.target.result;
			};
			reader.readAsDataURL(file);
		}
	}

	onVideoSelected(event: any): void {
		const file = event.target.files[0];
		if (file) {
			if (file.size > this.MAX_VIDEO_SIZE) {
				this.utils.showToast('El archivo de video no puede superar los 50MB', 'error');
				return;
			}
			this.videoFile = file;
		}
	}

	save(): void {
		if (this.courseForm.invalid) {
			this.utils.showToast('Por favor, complete todos los campos requeridos', 'error');
			return;
		}

		this.loading = true;
		const formData = new FormData();
		
		formData.append('title', this.courseForm.get('title').value);
		formData.append('description', this.courseForm.get('description').value || '');
		formData.append('video_url', this.courseForm.get('video_url').value || '');
		formData.append('price', this.courseForm.get('price').value || '');
		formData.append('is_free', this.courseForm.get('is_free').value ? '1' : '0');
		formData.append('order', this.courseForm.get('order').value || '0');
		formData.append('is_active', this.courseForm.get('is_active').value ? '1' : '0');

		// Añadir tags - FormData requiere enviar cada elemento del array individualmente
		// Usar tags[] para que Laravel lo reciba como array indexado
		const tags = this.tagsFormArray.value.filter(tag => tag.trim() !== '');
		tags.forEach((tag) => {
			formData.append('tags[]', tag.trim());
		});

		// Añadir archivos
		if (this.thumbnailFile) {
			formData.append('thumbnail', this.thumbnailFile);
		}
		if (this.videoFile) {
			formData.append('video', this.videoFile);
		}

		const url = this.isEdit 
			? endpoint('academy_courses_edit', { id: this.courseId })
			: endpoint('academy_courses_create');

		this.dataService.http.post(url, formData, { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					// Obtener el ID del curso creado o editado
					const createdCourseId = response.response?.id || response.response?.course?.id || response.response?.data?.id || this.courseId;
					
					// Si se creó un nuevo curso y hay materiales pendientes, subirlos
					if (!this.isEdit && createdCourseId && this.pendingMaterials.length > 0) {
						this.courseId = createdCourseId;
						this.isEdit = true; // Cambiar a modo edición para que los métodos funcionen
						this.uploadPendingMaterials();
					} else {
						this.utils.showToast(this.isEdit ? 'Curso actualizado correctamente' : 'Curso creado correctamente', 'success');
						if (this.isEdit) {
							this.router.navigate(['/academy/courses', this.courseId]);
						} else {
							this.router.navigate(['/academy/courses']);
						}
						this.loading = false;
					}
				} else {
					this.utils.showToast(response.messages?.join(', ') || 'Error al guardar curso', 'error');
					this.loading = false;
				}
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al guardar curso', 'error');
				this.loading = false;
			});
	}

	goBack(): void {
		if (this.isEdit) {
			this.router.navigate(['/academy/courses', this.courseId]);
		} else {
			this.router.navigate(['/academy/courses']);
		}
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

	/**
	 * Manejar selección de archivo de material
	 */
	onMaterialSelected(event: any): void {
		const file = event.target.files[0];
		if (!file) return;

		if (file.size > this.MAX_MATERIAL_SIZE) {
			this.utils.showToast('El archivo no puede superar los 50MB', 'error');
			event.target.value = '';
			return;
		}

		if (this.isEdit && this.courseId) {
			// Si estamos editando, subir directamente
			this.uploadMaterial(file, file.name, '');
		} else {
			// Si estamos creando, guardar temporalmente
			this.pendingMaterials.push(file);
			this.utils.showToast('Material agregado. Se subirá al guardar el curso.', 'info');
		}
		
		// Limpiar el input
		event.target.value = '';
	}

	/**
	 * Subir material
	 */
	uploadMaterial(file: File, displayName?: string, description?: string, callback?: () => void): void {
		if (!this.courseId) {
			if (callback) callback();
			return;
		}

		const wasLoading = this.loading;
		if (!wasLoading) {
			this.loading = true;
		}
		
		const formData = new FormData();
		formData.append('file', file);
		
		if (displayName) {
			formData.append('display_name', displayName);
		}
		if (description) {
			formData.append('description', description);
		}

		this.dataService.http.post(
			endpoint('academy_courses_materials_upload', { id: this.courseId }), 
			formData, 
			{ headers: this.dataService.headers }
		)
		.toPromise()
		.then((response: any) => {
			this.utils.logResponse(response);
			if (response.code === 200) {
				if (!callback) {
					this.utils.showToast('Material agregado correctamente', 'success');
				}
				if (this.isEdit) {
					this.loadCourse(); // Recargar para obtener la lista actualizada
				}
			} else {
				this.utils.showToast(response.messages?.join(', ') || 'Error al subir material', 'error');
			}
			if (!wasLoading) {
				this.loading = false;
			}
			if (callback) callback();
		})
		.catch((error: any) => {
			console.error(error);
			this.utils.showToast('Error al subir material', 'error');
			if (!wasLoading) {
				this.loading = false;
			}
			if (callback) callback();
		});
	}

	/**
	 * Eliminar material pendiente
	 */
	removePendingMaterial(index: number): void {
		if (index >= 0 && index < this.pendingMaterials.length) {
			this.pendingMaterials.splice(index, 1);
		}
	}

	/**
	 * Subir materiales pendientes después de crear el curso
	 */
	uploadPendingMaterials(): void {
		if (!this.courseId || this.pendingMaterials.length === 0) {
			this.utils.showToast('Curso creado correctamente', 'success');
			this.router.navigate(['/academy/courses']);
			this.loading = false;
			return;
		}

		// Subir el primer material pendiente
		const file = this.pendingMaterials.shift();
		this.uploadMaterial(file, file.name, '', () => {
			// Después de subir, continuar con el siguiente
			if (this.pendingMaterials.length > 0) {
				this.uploadPendingMaterials();
			} else {
				// Todos los materiales subidos
				this.utils.showToast('Curso creado correctamente con todos los materiales', 'success');
				this.router.navigate(['/academy/courses']);
				this.loading = false;
			}
		});
	}

	/**
	 * Eliminar material
	 */
	deleteMaterial(material: any): void {
		if (!confirm('¿Está seguro de que desea eliminar este material?')) {
			return;
		}

		this.loading = true;
		this.dataService.http.delete(
			endpoint('academy_courses_materials_delete', { 
				courseId: this.courseId, 
				materialId: material.id 
			}), 
			{ headers: this.dataService.headers }
		)
		.toPromise()
		.then((response: any) => {
			this.utils.logResponse(response);
			if (response.code === 200) {
				this.utils.showToast('Material eliminado correctamente', 'success');
				this.loadCourse(); // Recargar para obtener la lista actualizada
			} else {
				this.utils.showToast(response.messages?.join(', ') || 'Error al eliminar material', 'error');
			}
			this.loading = false;
		})
		.catch((error: any) => {
			console.error(error);
			this.utils.showToast('Error al eliminar material', 'error');
			this.loading = false;
		});
	}
}

