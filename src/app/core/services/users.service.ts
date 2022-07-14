import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { endpoint } from 'src/environments/environment';
import { User } from 'src/app/shared/models/user.model';

@Injectable({
	providedIn: 'root'
})
export class UsersService {

	get headers() {
		return new HttpHeaders()
			.set('Accept', 'application/json')
			.set('Authorization', sessionStorage.getItem('token'));
	}

	constructor(public http: HttpClient) {
	}

	// Users

	usersExport(type: string) {
		let url = endpoint('users_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getUser(id: number) {
		let url = endpoint('users_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveUser(data: any) {
		let url = endpoint('users_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	confirmUser(id: number) {
		let url = endpoint('users_confirm', { id });
		return this.http.put(url, { confirmed: 1 }, { headers: this.headers });
	}

	validateUser(id: number, status: number) {
		let url = endpoint('users_validate', { id });
		return this.http.put(url, { status }, { headers: this.headers });
	}

	editUser(data: any, id: number) {
		let url = endpoint('users_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	deleteUser(id: number) {
		let url = endpoint('users_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	userStatus(user: User) {

		let color, text;

		if (user.confirmed) {
			color = 'green';
			text = 'Verificado';
		} else {
			color = 'red';
			text = 'Pendiente de verificar';
		}

		return { color, text };
	}

	deleteDocumentOne(id: number){
		let url = endpoint('users_del_doc1', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	deleteDocumentTwo(id: number){
		let url = endpoint('users_del_doc2', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	// Representations

	representationsExport(type: string) {
		let url = endpoint('representations_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getRepresentation(id: number) {
		let url = endpoint('representations_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveRepresentation(data: any) {
		let url = endpoint('representations_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	validateRepresentation(id: number, status: number) {
		let url = endpoint('representations_validate', { id });
		return this.http.put(url, { status }, { headers: this.headers });
	}

	editRepresentation(data: any, id: number) {
		let url = endpoint('representations_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	deleteRepresentation(id: number) {
		let url = endpoint('representations_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}
}
