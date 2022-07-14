import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { endpoint } from 'src/environments/environment';

@Injectable({
	providedIn: 'root'
})
export class LoginService {

	get headers() {
		return new HttpHeaders()
			.set('Accept', 'application/json')
			.set('Authorization', sessionStorage.getItem('token'));
	}

	constructor(public http:HttpClient) {
	}

	async loginUser(data: any) {
		let url = endpoint('login');
		const response = await this.http.post(url, data)
			.toPromise();
		return response;
	}

	async logoutUser() {
		let url = endpoint('logout');
		const response = await this.http.post(url, null, { headers: this.headers })
			.toPromise();
		return response;
	}

	recoverPassword(data: any) {
		let url = endpoint('recover_password');
		return this.http.post(url, data);
	}
}
