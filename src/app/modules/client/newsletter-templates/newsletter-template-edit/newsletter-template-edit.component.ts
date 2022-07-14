import { Component, OnInit, OnChanges, SimpleChanges, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';

import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { NewsletterTemplate } from 'src/app/shared/models/communication.model';

import { formErrors, minLengthArray } from 'src/app/shared/validators';

@Component({
	selector: 'app-newsletter-template-edit',
	templateUrl: './newsletter-template-edit.component.html',
	styleUrls: ['./newsletter-template-edit.component.scss']
})
export class NewsletterTemplateEditComponent implements OnInit {

	@Input() newsletterTemplateId!: number;
	@Output() success: EventEmitter<any> = new EventEmitter();
	@Output() close: EventEmitter<any> = new EventEmitter();

	// Modals

	public modalTitle = '';

	// Modal form

	public newsletterTemplate: NewsletterTemplate = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }
	createForArray(data): FormGroup { return this.fb.group(data); }

	public editor = ClassicEditor;
	public ckOptions = null;

	constructor(
		public fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService,
		public communicationsService: CommunicationsService
	) {
	}

	ngOnInit(): void {

		this.ckOptions = this.utils.ckOptions;
	}

	ngOnChanges(changes: SimpleChanges): void {

		this.newsletterTemplateId = changes.newsletterTemplateId.currentValue;

		this.setFormFields();

		if (this.newsletterTemplateId) {

			this.modalTitle = 'Editar Plantilla de Newsletter';

			this.communicationsService.getNewsletterTemplate(this.newsletterTemplateId)
			.subscribe((data: BaseResponse<NewsletterTemplate>) => {

				this.utils.logResponse(data.response);

				if (data.code == 200) {
					this.newsletterTemplate = data.response;
				}

				this.form.patchValue(this.newsletterTemplate);

			}, (data: ErrorResponse) => {
				this.utils.showToast(data.error.messages, 'error');
				this.close.emit();
			});

		} else {
			this.modalTitle = 'Nueva Plantilla de Newsletter';
		}
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			name: ['', [Validators.required]],
			subject: [''],
			sender: ['', [Validators.required]],
			email: ['', [Validators.required]],
			content: [''],
		});
	}

	saveNewsletterTemplate() {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.newsletterTemplate);

			const data = new FormData();

			data.append('name', this.form.get('name').value);
			data.append('subject', this.form.get('subject').value);
			data.append('sender', this.form.get('sender').value);
			data.append('email', this.form.get('email').value);
			data.append('content', this.form.get('content').value);

			if (!this.newsletterTemplateId) {

				this.communicationsService.saveNewsletterTemplate(data)
				.subscribe(data => {
					this.utils.showToast('Creado correctamente');
					this.success.emit();
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});

			} else {

				data.append('id', this.newsletterTemplateId.toString());

				this.communicationsService.editNewsletterTemplate(data, this.newsletterTemplateId)
				.subscribe(data => {
					this.utils.showToast('Editado correctamente');
					this.success.emit();
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
}
