import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, throwError } from 'rxjs';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { RandomizerService } from 'src/app/core/services/randomizer.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Newsletter, NewsletterStatus, Blog, BlogStatus, Notification, NotificationTypes } from 'src/app/shared/models/communication.model';
import { catchError } from 'rxjs/operators';

@Injectable({
	providedIn: 'root'
})
export class CommunicationsService {

	public notificationsOn: boolean = true;
	public notificationsInterval: number = 60; // Seconds
	public notificationsIntervalObject: any = null;

	public notificationsTab: BehaviorSubject<string> = new BehaviorSubject<string>('auctions');

	public registers: BehaviorSubject<Array<Notification>> = new BehaviorSubject<Array<Notification>>([]);
	public documentation: BehaviorSubject<Array<Notification>> = new BehaviorSubject<Array<Notification>>([]);
	public auctions: BehaviorSubject<Array<Notification>> = new BehaviorSubject<Array<Notification>>([]);

	get headers() {
		return new HttpHeaders()
			.set('Accept', 'application/json')
			.set('Authorization', sessionStorage.getItem('token'));
	}

	constructor(
		public http: HttpClient,
		public utils: UtilsService,
		public randomizerService: RandomizerService
	) {
	}

	// Blog

	blogExport(type: string) {
		let url = endpoint('blog_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getBlog(id: number) {
		let url = endpoint('blog_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveBlog(data: any) {
		let url = endpoint('blog_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	editBlog(data: any, id: number) {
		let url = endpoint('blog_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	deleteBlog(id: number) {
		let url = endpoint('blog_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}


	blogStatus(blog: Blog) {

		let color, text;

		switch (blog.status_id)
		{
			case BlogStatus.PUBLISHED: color = 'cyan'; text = 'Publicada'; break;
			case BlogStatus.DRAFT: color = 'gray'; text = 'Borrador'; break;
			case BlogStatus.SCHEDULED: color = 'navy'; text = 'Programada'; break;
		}

		return { color, text };
	}

	// Membresia

	deleteMembresia(id: number) {
		let url = endpoint('membresia_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	saveMembresia(data: any) {
		let url = endpoint('membresia_create');
		return this.http.post(url, data, { headers: this.headers });
	}


	async getMembresiaUsers() {
		let url = endpoint('membresia_users');
		const response = this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}

	async getMembresiaAuctions() {
		let url = endpoint('membresia_auctions');
		const response = this.http.get(url, { headers: this.headers })
			.toPromise();
		return response;
	}


	// Newsletters

	newslettersExport(type: string) {
		let url = endpoint('newsletters_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getNewsletter(id: number) {
		let url = endpoint('newsletters_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveNewsletter(data: any) {
		let url = endpoint('newsletters_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	editNewsletter(data: any, id: number) {
		let url = endpoint('newsletters_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	deleteNewsletter(id: number) {
		let url = endpoint('newsletters_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	newsletterStatus(newsletter: Newsletter) {

		let color, text;

		switch (newsletter.status_id)
		{
			case NewsletterStatus.SENT: color = 'cyan'; text = 'Enviada'; break;
			case NewsletterStatus.DRAFT: color = 'gray'; text = 'Borrador'; break;
			case NewsletterStatus.SCHEDULED: color = 'navy'; text = 'Programada'; break;
		}

		return { color, text };
	}

	// Newsletter Templates

	newsletterTemplatesExport(type: string) {
		let url = endpoint('newsletter_templates_export', { type });
		return this.http.get(url, { headers: this.headers, responseType: 'blob' });
	}

	getNewsletterTemplate(id: number) {
		let url = endpoint('newsletter_templates_get', { id });
		return this.http.get(url, { headers: this.headers });
	}

	saveNewsletterTemplate(data: any) {
		let url = endpoint('newsletter_templates_create');
		return this.http.post(url, data, { headers: this.headers });
	}

	editNewsletterTemplate(data: any, id: number) {
		let url = endpoint('newsletter_templates_get', { id });
		return this.http.post(url, data, { headers: this.headers });
	}

	deleteNewsletterTemplate(id: number) {
		let url = endpoint('newsletter_templates_get', { id });
		return this.http.delete(url, { headers: this.headers });
	}

	// Notifications

	getNotifications() {

		let url = endpoint('notifications');
		this.http.get(url, { headers: this.headers })
		.subscribe((data: BaseResponse<any>) => {

			this.utils.logResponse(data.response);

			if (data.code == 200) {
				this.registers.next(data.response.registers);
				this.documentation.next(data.response.documents);
				this.auctions.next(data.response.auctions);
			}

		}, (data: ErrorResponse) => {
		});
	}

	numberNotSeen(key: string = 'all', id: number = null) {

		let notSeen = 0;

		if (key == 'all' || key == 'registers') {
			this.registers.getValue().forEach(item => {
				if (item.status == 0) { notSeen++; }
			});
		}

		if (key == 'all' || key == 'documents') {
			this.documentation.getValue().forEach(item => {
				if (item.status == 0) { notSeen++; }
			});
		}

		if (key == 'all' || key == 'auctions') {
			this.auctions.getValue().forEach(item => {
				notSeen += item.new_bids;
			});
		}

		return notSeen;
	}

	markAsSeen(notif: Notification) {

		let url = endpoint('notifications_status', { id: notif.id });
		this.http.post(url, null, { headers: this.headers }).subscribe();

		let value;

		switch (notif.type_id) {

			case NotificationTypes.REGISTER:

				value = this.registers.getValue();
				value.map(item => {
					if (item.id == notif.id) { item.status = 1; }
					return item;
				});
				break;

			case NotificationTypes.DOCUMENT:
			case NotificationTypes.DEPOSIT:
			case NotificationTypes.REPRESENTATION:

				value = this.documentation.getValue();
				value.map(item => {
					if (item.id == notif.id) { item.status = 1; }
					return item;
				});
				break;

			case NotificationTypes.BID:
			case NotificationTypes.AUCTION_END_WIN:
			case NotificationTypes.AUCTION_END:

				value = this.auctions.getValue();
				value.map(item => {
					if (item.id == notif.id) { item.status = 1; item.new_bids = 0; }
					return item;
				});
				break;
		}
	}

	markAllAsSeen(key: string = 'all', id: number = null) {

		// Call to mark as seen on API

		let form: any = {};
		if (key == 'bids' && id) { form.auction_id = id; }

		let url = endpoint('notifications_status_all', { key });
		this.http.post(url, form, { headers: this.headers }).subscribe();

		// Mark local notifications as seen

		let value;

		if (key == 'all' || key == 'registers') {
			value = this.registers.getValue();
			value.map(item => { item.status = 1; return item; });
			this.registers.next(value);
		}

		if (key == 'all' || key == 'documents') {
			value = this.documentation.getValue();
			value.map(item => { item.status = 1; return item; });
			this.documentation.next(value);
		}

		if (key == 'all' || key == 'auctions') {
			value = this.auctions.getValue();
			value.map(item => {
				item.status = 1;
				item.new_bids = 0;
				return item;
			});
			this.auctions.next(value);
		}

		if (key == 'all' || (key == 'bids' && id)) {
			value = this.auctions.getValue();
			value.map(item => {
				if (item.id == id) {
					item.status = 1;
					item.new_bids = 0;
					return item;
				}
			});
			this.auctions.next(value);
		}
	}
}
