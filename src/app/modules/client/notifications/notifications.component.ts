import { Component, OnInit, Input, TemplateRef, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';

import { UtilsService } from 'src/app/core/services/utils.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';
import { Notification, NotificationTypes } from 'src/app/shared/models/communication.model';
import { Auction } from 'src/app/shared/models/auction.model';

@Component({
	selector: 'app-notifications',
	templateUrl: './notifications.component.html',
	styleUrls: ['./notifications.component.scss']
})
export class NotificationsComponent implements OnInit {

	@Input() sideMenu!: boolean;

	public bidsMenu = false;

	// Modals

	@ViewChild('bidsModal') bidsModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	public auctionId: number;

	get selectedTab() { return this.communicationsService.notificationsTab.getValue(); }
	get registers() { return this.communicationsService.registers.getValue(); }
	get documentation() { return this.communicationsService.documentation.getValue(); }
	get auctions() { return this.communicationsService.auctions.getValue(); }

	private countdownInterval;
	public auctionsInfo: Array<any> = [];

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public communicationsService: CommunicationsService
	) {
	}

	ngOnInit(): void {

		this.countdown();

		this.countdownInterval = setInterval(() => this.countdown(), 1000);

		$('.notifications .tab').hide();

		$(`.notifications .tab-button[data-tab=${this.selectedTab}]`).addClass('active');
		$(`.notifications .tab[data-tab=${this.selectedTab}]`).show();
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {

		$('.notifications .tab-button').removeClass('active');
		$('.notifications .tab').hide();

		$(`.notifications .tab-button[data-tab=${this.selectedTab}]`).addClass('active');
		$(`.notifications .tab[data-tab=${this.selectedTab}]`).show();
	}

	convertDate(value: string): string {
		return this.utils.convertDate(value);
	}

	changeTab(evt) {

		let value = $(evt.target).data('tab');

		this.communicationsService.notificationsTab.next(value);

		$('.notifications .tab-button').removeClass('active');
		$('.notifications .tab').hide();

		$(`.notifications .tab-button[data-tab=${value}]`).addClass('active');
		$(`.notifications .tab[data-tab=${value}]`).show();
	}

	numberNotSeen(key: string, id: number = null) {
		return this.communicationsService.numberNotSeen(key, id);
	}

	markAllAsSeen() {
		this.communicationsService.markAllAsSeen(this.selectedTab);
	}

	markAsSeen(notif: Notification) {
		this.communicationsService.markAsSeen(notif);
	}

	docIcon(notif: Notification) {
		switch (notif.type_id) {
			case NotificationTypes.DOCUMENT: return 'fa fa-user-check';
			case NotificationTypes.DEPOSIT: return 'fa fa-shopping-cart';
			case NotificationTypes.REPRESENTATION: return 'fa fa-address-card';
		}
	}

	countdown() {

		this.auctionsInfo = this.auctions.map(atn => {

			let timeLeft = this.utils.timeDiffString(`${atn.end_date}`);

			let icon;

			if (timeLeft) {
				icon = 'pill-red fa-gavel';
			} else if (atn.max_bid > 0) {
				icon = 'pill-green fa-trophy';
			} else {
				icon = 'pill-gray fa-exclamation-circle';
			}

			return {
				timeLeft: timeLeft ?? '- -',
				timeLeftLabel: timeLeft ? 'Tiempo restante' : 'Subasta finalizada',
				bidLabel: timeLeft ? 'Mejor puja' : 'Puja ganadora',
				icon,
			}
		});
	}

	goto(notif: Notification) {

		switch (notif.type_id) {
			case NotificationTypes.REGISTER:
			case NotificationTypes.DOCUMENT:
				this.markAsSeen(notif);
				this.router.navigate(['/users', notif.user_id, 'edit']);
				break;
			case NotificationTypes.DEPOSIT:
				this.markAsSeen(notif);
				this.router.navigate(['/auctions', notif.auction_id, 'deposits']);
				break;
			case NotificationTypes.REPRESENTATION:
				this.markAsSeen(notif);
				this.router.navigate(['/representations']);
				break;
			case NotificationTypes.BID:
			case NotificationTypes.AUCTION_END_WIN:
			case NotificationTypes.AUCTION_END:
				this.router.navigate(['/auctions', notif.auction_id, 'users']);
				break;
		}
	}

	openDocument(notif: Notification, evt) {

		evt.preventDefault();
		evt.stopPropagation();

		this.markAsSeen(notif);

		if (notif.document) {
			window.open(notif['document']['path'], '_blank').focus();
		}
	}

	openBids(notif: Notification) {

		this.auctionId = notif.reference;

		if (this.sideMenu) {
			this.bidsMenu = true;
		} else {
			this.modalRef = this.modalService.show(this.bidsModal, { class: 'modal-md' });
		}
	}

	closeBids() {
		if (this.sideMenu) {
			this.bidsMenu = false;
		} else {
			this.modalRef.hide();
		}
	}
}
