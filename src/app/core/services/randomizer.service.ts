import { Injectable } from '@angular/core';

import { User } from 'src/app/shared/models/user.model';
import { Notification } from 'src/app/shared/models/communication.model';
import { Auction, Bid } from 'src/app/shared/models/auction.model';

const alias: Array<string> = [
	'Constantino', 'Balbina', 'Oliver', 'Encarnación', 'Jorge-Luis', 'Gloria',
];

const firstnames: Array<string> = [
	'Arturo', 'Aurelio', 'Irune', 'Mayte', 'Hortensia', 'Amaia', 'Alfredo', 'Emilio', 'César', 'Florentina',
];

const lastnames: Array<string> = [
	'Luna', 'Soto', 'Carrera', 'Kaur', 'Vargas', 'Cid', 'Picazo', 'Tudela', 'Calatayud', 'Mañas',
];

const documents: Array<string> = [
	'50368369W', 'C37508991', 'G24965857', '50840514A', 'P0860543H', '48293729P', '67987531Z', 'N2864401A',
];

const titles: Array<string> = [
	'Plaza de Garaje en Canovelles, Barcelona (M2)',
	'Terreno en Massanes, Girona',
	'Parcela en Abrera, Barcelona',
	'Parcela en Abrera, Barcelona-2',
	'Plaza de Garaje en Reus, Tarragona (M4)',
	'Plaza de Garaje en Mollet del Vallès, Barcelona (72(2))',
	'Local Comercial y Plazas de garaje en Figueres, Girona',
	'Trastero en Alcazar de San Juan, Ciudad Real (4)',
	'Lote de mobiliario en Los Cristianos, Santa Cruz de Tenerife',
];

@Injectable({
	providedIn: 'root'
})
export class RandomizerService {

	constructor(
	) { }

	random(min, max) { return Math.floor(Math.random() * (max - min + 1) + min); }

	boolean() { return this.random(0,1) === 1; }

	alias() {
		return alias[this.random(0, alias.length - 1)];
	}

	firstname() {
		return firstnames[this.random(0, firstnames.length - 1)];
	}

	lastname() {
		return lastnames[this.random(0, lastnames.length - 1)];
	}

	document() {
		return documents[this.random(0, documents.length - 1)];
	}

	title() {
		return titles[this.random(0, titles.length - 1)];
	}

	code(length) {
		let result = '';
		let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let charactersLength = characters.length;
		for ( let i = 0; i < length; i++ ) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}

	randomUser(): Notification {
		let firstname = this.firstname();
		let lastname = this.lastname();

		let today = new Date();
		let dd = String(today.getDate()).padStart(2, '0');
		let mm = String(today.getMonth() + 1).padStart(2, '0');
		let yyyy = today.getFullYear();
		let hh = String(today.getHours()).padStart(2, '0');
		let ii = String(today.getMinutes()).padStart(2, '0');
		let ss = String(today.getSeconds()).padStart(2, '0');

		return {
			title: `Nuevo usuario - ${firstname.toLowerCase()}_${lastname.toLowerCase()}`,
			subtitle: `${firstname} ${lastname} - ${firstname.toLowerCase()}_${lastname.toLowerCase()}@example.com`,
			user_id: 10,
			type_id: 1,
			type: 1,
			created_at: `${yyyy}-${mm}-${dd} ${hh}:${ii}:${ss}`,
			updated_at: `${yyyy}-${mm}-${dd} ${hh}:${ii}:${ss}`,
			seen: false,
		} as Notification;
	}

	// randomDocumentation(): Notification {
	// 	let document_path = '/admin/notifications-center';

	// 	let title = this.title();

	// 	let firstname = this.firstname();
	// 	let lastname = this.lastname();
	// 	let document_number = this.document();

	// 	let alias = this.alias();

	// 	let today = new Date();
	// 	let dd = String(today.getDate()).padStart(2, '0');
	// 	let mm = String(today.getMonth() + 1).padStart(2, '0');
	// 	let yyyy = today.getFullYear();

	// 	return {
	// 		alias,
	// 		created_at: `${yyyy}-${mm}-${dd}`,
	// 		document_number,
	// 		document_path,
	// 		firstname,
	// 		id: this.random(1,600),
	// 		lastname,
	// 		seen: false,
	// 		title,
	// 		user: `${firstname} ${lastname}`,
	// 		type_id: this.random(1,3),
	// 		validated: this.boolean(),
	// 	} as Notification;
	// }

	randomAuction(): Auction {
		let title = this.title();

		let bid = this.random(1,10) == 1 ? null : this.randomBid();

		let today = new Date();
		let dd = String(today.getDate()).padStart(2, '0');
		let mm = String(today.getMonth() + 1).padStart(2, '0');
		let yyyy = today.getFullYear();

		let endDate;

		if (bid) {
			let end = new Date(today.getTime() + this.random(1000*60*60, 1000*60*60*24*3));
			let enddd = String(end.getDate()).padStart(2, '0');
			let endmm = String(end.getMonth() + 1).padStart(2, '0');
			let endyyyy = end.getFullYear();
			let endhh = String(end.getHours()).padStart(2, '0');
			let endii = String(end.getMinutes()).padStart(2, '0');
			let endss = String(end.getSeconds()).padStart(2, '0');
			endDate = `${endyyyy}-${endmm}-${enddd} ${endhh}:${endii}:${endss}`;
		} else {
			endDate = `${yyyy}-${mm}-${dd}`;
		}

		return {
			bid_list: bid ? [bid] : [],
			created_at: `${yyyy}-${mm}-${dd}`,
			end_date: endDate,
			id: this.random(1,600),
			last_bid: bid ? bid.price : 0,
			seen: bid ? false : true,
			title,
		} as Auction;
	}

	randomBid(lastPrice: number = null): Bid {

		let today = new Date();
		let dd = String(today.getDate()).padStart(2, '0');
		let mm = String(today.getMonth() + 1).padStart(2, '0');
		let yyyy = today.getFullYear();

		let price;
		if (lastPrice) {
			price = this.random(lastPrice+100, lastPrice+800);
		} else {
			price = this.random(100, 100+800);
		}

		let firstname = this.firstname();
		let lastname = this.lastname();

		return {
			auction_id: 1,
			created_at: `${yyyy}-${mm}-${dd}`,
			id: 1,
			price,
			seen: false,
			user: `${firstname} ${lastname}`,
		} as Bid;
	}
}
