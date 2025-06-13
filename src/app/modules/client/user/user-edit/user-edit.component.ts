import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { UsersService } from 'src/app/core/services/users.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Role, Country, Province } from 'src/app/shared/models/data.model';
import { User } from 'src/app/shared/models/user.model';

import { formErrors } from 'src/app/shared/validators';

@Component({
	selector: 'app-user-edit',
	templateUrl: './user-edit.component.html',
	styleUrls: ['./user-edit.component.scss']
})
export class UserEditComponent implements OnInit {


	public createdAt: string = '';
	public userStatus: string = '';
	public userStatusColor: string = '';

	// Form

	public userId: number;
	public user: User = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }

	// Selectors

	public roles: Role[] = [];
	public countries: Country[] = [];
	public provinces: Province[] = [];

	// Modals

	@ViewChild('confirmConfirmModal') confirmConfirmModal: TemplateRef<any>;
	@ViewChild('confirmValidateModal') confirmValidateModal: TemplateRef<any>;
	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;

	constructor(
		private router: Router,
		private route: ActivatedRoute,
		public utils: UtilsService,
		public dataService: DataService,
		private modalService: BsModalService,
		public fb: FormBuilder,
		public userService: UsersService
	) {
	}

	reloadComponent() {
		this.router.routeReuseStrategy.shouldReuseRoute = () => false;
		this.router.onSameUrlNavigation = 'reload';
		this.router.navigate(['/users', this.userId, 'edit']);
	}

	ngOnInit(): void {

		this.dataService.getRoles().then((val: Role) => {
			this.roles = val['response'];
		});

		this.dataService.getCountries().then((val: Country) => {
			this.countries = val['response'];
		});

		this.setFormFields();

		this.route.params.subscribe(params => {

			if (!params['id']) { this.router.navigate(['/users']); }

			this.userId = params['id'];

			this.getUser();
		});
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

		let value = this.form?.get('province_id').value;

		this.dataService.getProvinces(country).then((val: Province) => {
			this.provinces = val['response'];
			this.provinces.unshift({ id: null, name: '' } as Province);

			setTimeout(() => {
				if (this.provinces.find(p => p.id == value)) {
					this.form?.get('province_id').setValue(value);
				} else if (this.provinces.find(p => p.id == this.user.province_id)) {
					this.form?.get('province_id').setValue(this.user.province_id);
				} else {
					this.form?.get('province_id').setValue(null);
				}
			}, 100);
		});
	}

	getUser() {

		this.userService.getUser(this.userId)
		.subscribe((data: BaseResponse<User>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.user = data.response;

				this.createdAt = formatDate(this.user.created_at, 'dd/MM/yyyy HH:mm:ss', 'es');

				let styles = this.userService.userStatus(this.user);
				this.userStatus = styles.text;
				this.userStatusColor = 'text-'+styles.color;
			}

			this.getProvinces(this.user.country_id);

			this.form.patchValue(this.user);

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
			this.router.navigate(['/users']);
		});
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			id: ['', [Validators.required]],
			username: ['', [Validators.required]],
			role_id: ['', [Validators.required]],
			firstname: ['', [Validators.required]],
			lastname: ['', [Validators.required]],
			document_number: ['', [Validators.required]],
			document: [''],
			document_two: [''],
			email: ['', [Validators.required]],
			phone: ['', [Validators.required]],
			birthdate: ['', [Validators.required]],
			password: [''],
			password_confirmation: [''],
			address: ['', [Validators.required]],
			cp: [''],
			city: ['', [Validators.required]],
			province_id: ['', [Validators.required]],
			country_id: ['', [Validators.required]],
			confirmed: ['', [Validators.required]],
		});
console.log("SetFormFields funcion, ya valido campos");
console.log(this.form);
		// Change provinces when country changes
		this.form.get('country_id').valueChanges.subscribe(result => {
			this.getProvinces(result);
		});
	}

	uploadDocument(event: any) {

		if (event.target.files && event.target.files[0]) {
			const reader = new FileReader();
			reader.onload = () => {
				this.form.get('document').setValue(event.target.files[0]);
			};
			reader.readAsDataURL(event.target.files[0]);
		}
	}

	uploadDocumentTwo(event: any) {

		if (event.target.files && event.target.files[0]) {
			const reader = new FileReader();
			reader.onload = () => {
				this.form.get('document_two').setValue(event.target.files[0]);
			};
			reader.readAsDataURL(event.target.files[0]);
		}
	}

	checkAddress() {
		return !this.user.address || !this.user.cp || !this.user.city || !this.user.province_id || !this.user.country_id;
	}

	editUser() {

		this.submitted = true;
console.log("funcion editUser");
		if (this.form.valid) {

			this.utils.formToObject(this.form, this.user);

			const data = new FormData();

			data.append('id', this.form.get('id').value);
			data.append('username', this.form.get('username').value);
			data.append('role_id', this.form.get('role_id').value);
			data.append('firstname', this.form.get('firstname').value);
			data.append('lastname', this.form.get('lastname').value);
			data.append('document_number', this.form.get('document_number').value);
			data.append('document', this.form.get('document').value);
			data.append('document_two', this.form.get('document_two').value);
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
			data.append('confirmed', this.form.get('confirmed').value);

			this.userService.editUser(data, this.userId)
			.subscribe(data => {
				this.utils.showToast('Editado correctamente');
			}, data => {
				if (data.error.code == 401) {
					this.utils.parseResponseErrors(this.form, data);
					this.utils.showToast('1 Formulario incorrecto', 'error');
				} else {
					this.utils.showToast(data.error.messages, 'error');
				}
			});

		} else {
			const camposInvalidos: string[] = [];
  
			Object.keys(this.form.controls).forEach((campo) => {
			const control = this.form.get(campo);
				if (control?.invalid) {
				  camposInvalidos.push(campo);
				}
			});

			console.log(camposInvalidos);

			this.utils.showToast('2 Formulario incorrecto', 'error');
		}
	}

	confirmConfirmUser() {
		if (this.user.confirmed) { return; }
		this.modalRef = this.modalService.show(this.confirmConfirmModal, { class: 'modal-xs' });
	}

	confirmUser() {

		this.userService.confirmUser(this.userId)
		.subscribe(data => {
			this.utils.showToast('El usuario ha sido confirmado');
			this.reloadComponent();
			this.modalRef.hide();
		});
	}

	confirmValidateUser() {
		if (this.user.status != 0) { return; }
		this.modalRef = this.modalService.show(this.confirmValidateModal, { class: 'modal-md' });
	}

	validateUser(status: number) {

		this.userService.validateUser(this.userId, status)
		.subscribe(data => {
			if (status == 1) {
				this.utils.showToast('La documentación del usuario ha sido validada');
			} else if (status == 2) {
				this.utils.showToast('La documentación del usuario ha sido invalidada');
			}
            this.reloadComponent();
            this.modalRef.hide();
		});
	}

	confirmDelete() {
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteUser() {

		this.userService.deleteUser(this.userId)
		.subscribe(data => {
			this.router.navigate(['/users']);
			this.utils.showToast('El usuario ha sido borrado');
			this.modalRef.hide();
		});
	}

	deleteDocumentOne() {

		this.userService.deleteDocumentOne(this.userId)
		.subscribe(data => {
			this.utils.showToast('El documento ha sido borrado');
			this.reloadComponent();
			this.modalRef.hide();
		});
	}

	deleteDocumentTwo() {

		this.userService.deleteDocumentTwo(this.userId)
		.subscribe(data => {
			this.utils.showToast('El documento ha sido borrado');
			this.reloadComponent();
			this.modalRef.hide();
		});
	}
}
