import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { endpoint } from 'src/environments/environment';

@Injectable({
	providedIn: 'root'
})
export class DataService {

	get headers() {
		return new HttpHeaders()
			.set('Accept', 'application/json')
			.set('Authorization', sessionStorage.getItem('token'));
	}

	constructor(
		public http: HttpClient
	) {
	}

	async getAssetCategories() {
		let url = endpoint('asset_categories_list');
		const response = this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getAuctionAssets() {
		let url = endpoint('auction_assets_list');
		const response = this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getConditions() {
		let url = endpoint('conditions_list');
		const response = this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getCountries() {
		let url = endpoint('countries_list');
		const response = await this.http.get(url)
			.toPromise();
		return response;
	}

	async getNewsletterTemplates() {
		let url = endpoint('newsletter_templates_list');
		const response = await this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getProvinces(country: any = null) {
		country = country ? country : '';
		let url = endpoint('provinces_list', { country });
		const response = await this.http.get(url)
			.toPromise();
		return response;
	}

	async getRepresentationTypes() {
		let url = endpoint('representation_types_list');
		const response = this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getRepresentationUsers() {
		let url = endpoint('users_dt');
		const response = this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getRoles() {
		let url = endpoint('roles_list');
		const response = await this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getMembresiaUsers() {
		let url = endpoint('membresia_users');
		const response = await this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getMembresiaAuctions() {
		let url = endpoint('membresia_auctions');
		const response = await this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}
}
