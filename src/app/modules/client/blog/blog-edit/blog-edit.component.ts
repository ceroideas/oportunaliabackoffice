import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Blog, BlogStatus } from 'src/app/shared/models/communication.model';

import { formErrors } from 'src/app/shared/validators';

@Component({
	selector: 'app-blog-edit',
	templateUrl: './blog-edit.component.html',
	styleUrls: ['./blog-edit.component.scss']
})
export class BlogEditComponent implements OnInit {


	editHtml = false;

	// Form

	public blog: Blog = {} as any;
	public blogId: number;
	public blogStatus: any = BlogStatus;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }

	public editor = ClassicEditor;
	public ckOptions = null;

	constructor(
		private router: Router,
		private route: ActivatedRoute,
		public fb: FormBuilder,
		public utils: UtilsService,
		public communicationsService: CommunicationsService
	) {
	}

	ngOnInit(): void {

		this.ckOptions = this.utils.ckOptions;

		this.setFormFields();

		this.route.params.subscribe(params => {

			this.blogId = params['id'];

			if (this.blogId) {
				this.getBlog();
			}
		});
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {
	}

	getBlog() {

		this.communicationsService.getBlog(this.blogId)
		.subscribe((data: BaseResponse<Blog>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.blog = data.response;

				if (this.blog.show_date) {
					let showDate = this.utils.extractDateFrom(this.blog.show_date);
					let showTime = this.utils.extractTimeFrom(this.blog.show_date);
					this.blog.show_date = showDate;
					this.blog.show_time = showTime;
				}
			}

			this.form.patchValue(this.blog);

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
			this.router.navigate(['/auctions']);
		});
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			status_id: [''],
			title: ['', [Validators.required]],
			show_date: [''],
			show_time: [''],
			content: ['', [Validators.required]],
		});
	}

	saveBlog(status = null) {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.blog);

			const data = new FormData();

			let showDate = this.form.get('show_date').value;
			let showTime = this.form.get('show_time').value;
			showTime = showTime ? showTime : '00:00';
			showDate = showDate ? `${showDate} ${showTime}` : '';

			data.append('status_id', status ? status : this.form.get('status_id').value);
			data.append('title', this.form.get('title').value);
			data.append('show_date', `${showDate}`);
			data.append('content', this.form.get('content').value);

			if (!this.blogId) {

				this.communicationsService.saveBlog(data)
				.subscribe(data => {
					this.router.navigate(['/blog']);
					this.utils.showToast('Añadido correctamente');
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});

			} else {

				data.append('id', this.blogId.toString());

				this.communicationsService.editBlog(data, this.blogId)
				.subscribe(data => {
					this.router.navigate(['/blog']);
					this.utils.showToast('Editado correctamente');
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

	editAsHtml(){
		this.editHtml = !this.editHtml;
	}
}
