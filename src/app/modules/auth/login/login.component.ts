import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';

import { environment } from 'src/environments/environment';
import { LoginService } from 'src/app/core/services/login.service';
import { UtilsService } from 'src/app/core/services/utils.service';
import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Login } from 'src/app/shared/models/login.model';

@Component({
	selector: 'app-login',
	templateUrl: './login.component.html',
	styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

	public form: FormGroup

	constructor(
		public fb: FormBuilder,
		public utils: UtilsService,
		public loginService: LoginService,
		public router: Router
	) {
	}

	ngOnInit(): void {

		this.form = this.fb.group({
			email: '',
			password: '',
		});
	}

	loginUser() {

		this.utils.showToast('Comprobando...', 'info');

		let data = {
			email: this.form.get('email').value,
			password: this.form.get('password').value,
		};

		this.loginService.loginUser(data)
		.then((data: BaseResponse<Login>) => {

			if (data.code == 200) {

				sessionStorage.setItem('token', data.response.token);
				sessionStorage.setItem('email', data.response.email);
				sessionStorage.setItem('firstname', data.response.firstname);
				sessionStorage.setItem('lastname', data.response.lastname);

				this.utils.showToast('Bienvenido', 'success');

				setTimeout(() => {
					this.router.navigateByUrl(environment.defaultRoute);
				}, 1000);

			} else {
				this.utils.showToast(data.messages, 'error');
			}

		}, (data: ErrorResponse) => {
			this.utils.showToast(data.error.messages, 'error');
		});
	}
}
