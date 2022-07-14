import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { environment, endpoint } from 'src/environments/environment';

@Injectable({
	providedIn: 'root'
})
export class AssetsService {

	get headers() {
		return new HttpHeaders()
			.set('Accept', 'application/json')
			.set('Authorization', sessionStorage.getItem('token'));
	}

	constructor(public http: HttpClient) {
	}

	// Assets

	assetsExport(type: string) {
		let url = endpoint('assets_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getAsset(id: number) {
		let url = endpoint('assets_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveAsset(data: any) {
		let url = endpoint('assets_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	editAsset(data: any, id: number) {
		let url = endpoint('assets_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	deleteAsset(id: number) {
		let url = endpoint('assets_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	deleteAssetImage(id: number) {
		let url = endpoint('assets_image_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	duplicateAsset(id: number) {
		let url = endpoint('assets_duplicate', { id });
		return this.http.get(url, { headers: this.headers });
	}

	openMaps(query: string) {
		let url = endpoint(environment.google_maps, { query });
		window.open(url, '_blank');
	}

	// Asset Categories

	assetCategoriesExport(type: string) {
		let url = endpoint('asset_categories_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getAssetCategory(id: number) {
		let url = endpoint('asset_categories_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveAssetCategory(data: any) {
		let url = endpoint('asset_categories_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	editAssetCategory(data: any, id: number) {
		let url = endpoint('asset_categories_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	deleteAssetCategory(id: number) {
		let url = endpoint('asset_categories_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}
}
