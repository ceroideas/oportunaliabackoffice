import { Component, OnInit, OnChanges, SimpleChanges, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { UsersService } from 'src/app/core/services/users.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Country, Province } from 'src/app/shared/models/data.model';
import { User, Representation, RepresentationType } from 'src/app/shared/models/user.model';

import { formErrors } from 'src/app/shared/validators';

@Component({
	selector: 'app-representation-edit',
	templateUrl: './representation-edit.component.html',
	styleUrls: ['./representation-edit.component.scss']
})
export class RepresentationEditComponent implements OnInit {

	@Input() representationId!: number;
	@Input() userId!: number;
	@Output() success: EventEmitter<any> = new EventEmitter();
	@Output() close: EventEmitter<any> = new EventEmitter();

	// Selectors

	public types: RepresentationType[] = [];
	public users: User[] = [];
	public countries: Country[] = [];
	public provinces: Province[] = [];

	// Modals

	public modalTitle = '';

	// Modal form

	public representation: Representation = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }

	constructor(
		public fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService,
		public userService: UsersService
	) {
	}

	ngOnInit(): void {

		this.dataService.getCountries().then((val: Country) => {
			this.countries = val['response'];
		});

		this.dataService.getRepresentationTypes()
		.then((val: RepresentationType) => {
			this.types = val['response'];
		});

		this.dataService.getRepresentationUsers()
		.then((val: User) => {
			this.users = val['response'];
		});
	}

	ngOnChanges(changes: SimpleChanges): void {

		this.representationId = changes.representationId.currentValue;

		this.setFormFields(!!this.representationId);

		if (this.representationId) {

			this.modalTitle = 'Editar Representación de Usuario';

			this.userService.getRepresentation(this.representationId)
			.subscribe((data: BaseResponse<Representation>) => {

				this.utils.logResponse(data.response);

				if (data.code == 200) {
					this.representation = data.response;

					this.getProvinces(this.representation.country_id);

					this.form.patchValue(this.representation);
				}

			}, (data: ErrorResponse) => {
				this.utils.showToast(data.error.messages, 'error');
				this.close.emit();
			});

		} else {

			this.modalTitle = 'Nueva Representación de Usuario';

			this.getProvinces();
		}
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {
	}

	getProvinces(country: any = null) {

		let value = this.form?.get('province_id').value;

		this.dataService.getProvinces(country).then((val: Province) => {
			this.provinces = val['response'];
			this.provinces.unshift({ id: null, name: '' } as Province);

			setTimeout(() => {
				if (this.provinces.find(p => p.id == value)) {
					this.form?.get('province_id').setValue(value);
				} else if (this.provinces.find(p => p.id == this.representation.province_id)) {
					this.form?.get('province_id').setValue(this.representation.province_id);
				} else {
					this.form?.get('province_id').setValue(null);
				}
			}, 50);
		});
	}

	setFormFields(edit = false) {

		this.submitted = false;

		let group = {
			id: [''],
			user_id: ['', [Validators.required]],
			alias: ['', [Validators.required]],
			representation_type_id: ['', [Validators.required]],
			firstname: ['', [Validators.required]],
			lastname: ['', [Validators.required]],
			document_number: ['', [Validators.required]],
			use_user_address: [''],
			file: ['', [Validators.required]],
			address: [''],
			cp: [''],
			city: [''],
			province_id: [''],
			country_id: [''],
		};

		if (edit) {
			group.file = [''];
		}

		this.form = this.fb.group(group);

		// Change provinces when country changes
		this.form.get('country_id').valueChanges.subscribe(result => {
			this.getProvinces(result);
		});
	}

	uploadDocumentFile(event: any) {

		if (event.target.files && event.target.files[0]) {
			const reader = new FileReader();
			reader.onload = () => {
				this.form.get('file').setValue(event.target.files[0]);
			};
			reader.readAsDataURL(event.target.files[0]);
		}
	}

	saveRepresentation() {

		this.submitted = true;
		let representationId = this.form.get('id').value;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.representation);

			const data = new FormData();

			data.append('user_id', this.form.get('user_id').value);
			data.append('alias', this.form.get('alias').value);
			data.append('representation_type_id', this.form.get('representation_type_id').value);
			data.append('firstname', this.form.get('firstname').value);
			data.append('lastname', this.form.get('lastname').value);
			data.append('document_number', this.form.get('document_number').value);
			data.append('use_user_address', this.form.get('use_user_address').value ? '1' : '0');
			data.append('file', this.form.get('file').value);
			data.append('address', this.form.get('address').value);
			data.append('cp', this.form.get('cp').value);
			data.append('city', this.form.get('city').value);
			data.append('province_id', this.form.get('province_id').value);
			data.append('country_id', this.form.get('country_id').value);

			if (!representationId) {

				this.userService.saveRepresentation(data)
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

				data.append('id', representationId);

				this.userService.editRepresentation(data, representationId)
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
