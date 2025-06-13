import { User } from './user.model';
import { Auction } from './auction.model';

export const NewsletterStatus: any = {
	SENT: 1,
	DRAFT: 2,
	SCHEDULED: 3,
}

export interface Newsletter {
	auctions: Array<Auction>,
	content: string,
	created_at: string,
	email: string,
	id: number,
	send_date: string,
	send_time: string,
	sent_date: string,
	sender: string,
	status: string,
	status_id: number,
	subject: string,
	template: string,
	template_id: number,
	title: string,
}

export interface NewsletterTemplate {
	content: string,
	created_at: string,
	email: string,
	id: number,
	name: string,
	sender: string,
	subject: string,
}

export const BlogStatus: any = {
	PUBLISHED: 1,
	DRAFT: 2,
	SCHEDULED: 3,
}

export interface Blog {
	content: string,
	created_at: string,
	id: number,
	show_date: string,
	show_time: string,
	pubish_date: string,
	status_id: number,
	status: string,
	title: string,
	views: number,
}

export interface Membresia {
	id: number,
	note: string,
	auction_id: number,
	user_id: number,
}

export const NotificationTypes: any = {
	REGISTER: 1,
	DOCUMENT: 2,
	DEPOSIT: 3,
	REPRESENTATION: 4,
	BID: 5,
	AUCTION_END_WIN: 6,
	AUCTION_END: 7,
}

export interface Notification {
	id: number,
	title: string,
	subtitle: string,
	user_id: number,
	auction_id: number,
	representation_id: number,
	type_id: number,
	type: number,
	new_bids: number,
	max_bid: number,
	__max_bid: number,
	is_best_bid: boolean,
	end_date: string,
	created_at: string,
	updated_at: string,
	reference: number,
	status: number,
	seen: boolean,
	document: any,
}
