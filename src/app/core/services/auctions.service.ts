import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { endpoint } from 'src/environments/environment';
import { Auction, AuctionStatus } from 'src/app/shared/models/auction.model';

@Injectable({
	providedIn: 'root'
})
export class AuctionsService {

	get headers() {
		return new HttpHeaders()
			.set('Accept', 'application/json')
			.set('Authorization', sessionStorage.getItem('token'));
	}

	constructor(public http: HttpClient) {
	}

	// Auctions

	auctionsExport(type: string) {
		let url = endpoint('auctions_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getAuction(id: number) {
		let url = endpoint('auctions_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	getAuctionActivity(id: number) {
		let url = endpoint('auction_activity', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveAuction(data: any) {
		let url = endpoint('auctions_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	editAuction(data: any, id: number) {
		let url = endpoint('auctions_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	featureAuction(id: number) {
		let url = endpoint('auction_featured', { id });
		return this.http.put(url, null, { headers: this.headers });
	}

	asignarAuction(id: number) {
		let url = endpoint('auction_asignado', { id });
		return this.http.put(url, null, { headers: this.headers });
	}

	duplicateAuction(id: number) {
		let url = endpoint('auction_duplicate', { id });
		return this.http.get(url, { headers: this.headers });
	}

	duplicateDirectSelling(id: number) {
		let url = endpoint('direct_selling_duplicate', { id });
		return this.http.get(url, { headers: this.headers });
	}

	deleteAuction(id: number) {
		let url = endpoint('auctions_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	auctionFinalReport(id: number) {
		let url = endpoint('auction_final_report', { id });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	directSaleFinalReport(id: number) {
		let url = endpoint('direct_sale_final_report', { id });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	auctionStatus(auction: Auction) {

		let color, text;

		switch (auction.auction_status_id) {
			case AuctionStatus.DRAFT: color = 'gray'; text = 'Borrador'; break;
			case AuctionStatus.SOON: color = 'purple'; text = 'Próximamente'; break;
			case AuctionStatus.ONGOING: color = 'cyan'; text = 'En curso'; break;
			case AuctionStatus.FINISHED: color = 'green'; text = 'Finalizada'; break;
			case AuctionStatus.ARCHIVED: color = 'navy'; text = 'Archivada'; break;
			case AuctionStatus.SOLD: color = 'green'; text = 'Vendida'; break;
			case AuctionStatus.UNSOLD: color = 'red'; text = 'No vendida'; break;
		}

		return { color, text };
	}

	// Deposits

	depositsExport(type: string) {
		let url = endpoint('deposits_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	validateDeposit(id: number, status: number) {
		let url = endpoint('deposits_validate', { id });
		return this.http.put(url, { status }, { headers: this.headers });
	}

	// Direct Sellings

	directSellingsExport(type: string) {
		let url = endpoint('direct_sellings_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getDirectSelling(id: number) {
		let url = endpoint('direct_sellings_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveDirectSelling(data: any) {
		let url = endpoint('direct_sellings_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	editDirectSelling(data: any, id: number) {
		let url = endpoint('direct_sellings_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	featureDirectSelling(id: number) {
		let url = endpoint('direct_selling_featured', { id });
		return this.http.put(url, null, { headers: this.headers });
	}

	asignarDirectSelling(id: number) {
		let url = endpoint('direct_selling_asignado', { id });
		return this.http.put(url, null, { headers: this.headers });
	}
	
	deleteDirectSelling(id: number) {
		let url = endpoint('direct_sellings_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	// Direct Selling Offers

	validateOffer(id: number, status: number) {
		let url = endpoint('offers_validate', { id });
		return this.http.put(url, { status }, { headers: this.headers });
	}

	deleteDocument(id: number , document: any){
		let url = endpoint('delete_document', { id });
		return this.http.put(url, { document } , { headers: this.headers });
	}
}
