import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { LoginService } from 'src/app/core/services/login.service';
import { UtilsService } from 'src/app/core/services/utils.service';

import { formErrors } from 'src/app/shared/validators';

@Component({
	selector: 'app-forgot-password',
	templateUrl: './forgot-password.component.html',
	styleUrls: ['./forgot-password.component.scss']
})
export class ForgotPasswordComponent implements OnInit {

	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type = null) { return formErrors(this.form, this.submitted, key, type); }

	constructor(
		public fb: FormBuilder,
		public utils: UtilsService,
		public loginService: LoginService,
		public router: Router
	) { }

	ngOnInit(): void {

		this.form = this.fb.group({
			email: ['', [Validators.required]],
		});
	}

	recoverPassword() {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.showToast('Comprobando...', 'info');

			let data = {
				email: this.form.get('email').value,
			};

			this.loginService.recoverPassword(data)
			.subscribe((response: any) => {

				if (response.code == 200) {

					this.utils.showToast('Email de reestablecimiento de contraseña enviado', 'success');

					setTimeout(() => {
						this.router.navigateByUrl('/login');
					}, 1000);

				} else {
					this.utils.showToast('Email erróneo', 'error');
				}

			}, error => {
				this.utils.showToast('Ha ocurrido un error', 'error');
			});
		}
	}
}
