import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { UsersService } from 'src/app/core/services/users.service';

import { Role, Country, Province } from 'src/app/shared/models/data.model';
import { User } from 'src/app/shared/models/user.model';

import { formErrors } from 'src/app/shared/validators';

@Component({
	selector: 'app-user-create',
	templateUrl: './user-create.component.html',
	styleUrls: ['./user-create.component.scss']
})
export class UserCreateComponent implements OnInit {

	// Form

	public user: User = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }

	// Selectors

	public roles: Role[] = [];
	public countries: Country[] = [];
	public provinces: Province[] = [];

	constructor(
		private router: Router,
		public fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService,
		public userService: UsersService
	) {
	}

	ngOnInit(): void {

		this.dataService.getRoles().then((val: Role) => {
			this.roles = val['response'];
		});

		this.dataService.getCountries().then((val: Country) => {
			this.countries = val['response'];
		});

		this.getProvinces();

		this.setFormFields();
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {

		let that = this;

		// Only numeric characters
		$('[formcontrolname=phone]').on('keyup', () => {
			let value = that.form?.controls.phone.value;
			value = value.replace(/[^\d+\s]/gi, '');
			that.form?.controls.phone.setValue(value);
		});
	}

	getProvinces(country: any = null) {

		this.dataService.getProvinces(country).then((val: Province) => {
			this.provinces = val['response'];
			this.provinces.unshift({ id: null, name: '' } as Province);
		});
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			username: ['', [Validators.required]],
			role_id: ['', [Validators.required]],
			firstname: ['', [Validators.required]],
			lastname: ['', [Validators.required]],
			document_number: ['', [Validators.required]],
			document: [''],
			email: ['', [Validators.required]],
			phone: ['', [Validators.required]],
			birthdate: ['', [Validators.required]],
			password: ['', [Validators.required, Validators.min(8)]],
			password_confirmation: ['', [Validators.required]],
			address: ['', [Validators.required]],
			cp: ['', [Validators.required]],
			city: ['', [Validators.required]],
			province_id: ['', [Validators.required]],
			country_id: ['', [Validators.required]],
		});

		// Change provinces when country changes
		this.form.get('country_id').valueChanges.subscribe(result => {
			this.getProvinces(result);
		});
	}

	uploadDocumentFile(event: any) {

		if (event.target.files && event.target.files[0]) {
			const reader = new FileReader();
			reader.onload = () => {
				this.form.get('document').setValue(event.target.files[0]);
			};
			reader.readAsDataURL(event.target.files[0]);
		}
	}

	saveUser() {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.user);

			const data = new FormData();

			data.append('username', this.form.get('username').value);
			data.append('role_id', this.form.get('role_id').value);
			data.append('firstname', this.form.get('firstname').value);
			data.append('lastname', this.form.get('lastname').value);
			data.append('document_number', this.form.get('document_number').value);
			data.append('document', this.form.get('document').value);
			data.append('email', this.form.get('email').value);
			data.append('phone', this.form.get('phone').value);
			data.append('birthdate', this.form.get('birthdate').value);
			data.append('password', this.form.get('password').value);
			data.append('password_confirmation', this.form.get('password_confirmation').value);
			data.append('address', this.form.get('address').value);
			data.append('cp', this.form.get('cp').value);
			data.append('city', this.form.get('city').value);
			data.append('province_id', this.form.get('province_id').value);
			data.append('country_id', this.form.get('country_id').value);

			this.userService.saveUser(data)
			.subscribe(data => {
				this.router.navigate(['/users']);
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
			this.utils.showToast('Formulario incorrecto', 'error');
		}
	}
}
