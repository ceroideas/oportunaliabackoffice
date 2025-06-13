import { Component, OnInit, OnChanges, SimpleChanges, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { formatDate } from '@angular/common';

import { UtilsService } from 'src/app/core/services/utils.service';
import { AuctionsService } from 'src/app/core/services/auctions.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Notification, NotificationTypes } from 'src/app/shared/models/communication.model';
import { Auction, AuctionStatus } from 'src/app/shared/models/auction.model';

@Component({
	selector: 'app-notification-bids',
	templateUrl: './notification-bids.component.html',
	styleUrls: ['./notification-bids.component.scss']
})
export class NotificationBidsComponent implements OnInit, OnChanges {

	public lastBidLabel: string = '';
	public timeLeftLabel: string = '';

	@Input() auctionId!: number;
	@Input() sideMenu!: boolean;
	@Output() close: EventEmitter<any> = new EventEmitter();

	// Auction

	public auction: Auction = {} as any;
	private notificationsInterval;
	private countdownInterval;
	public time_left: string = null;

	public images: any = [];
	public imageIndex: number = 0;

	constructor(
		private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public communicationsService: CommunicationsService,
		public auctionsService: AuctionsService
	) {
	}

	ngOnInit(): void {

		if (this.communicationsService.notificationsOn) {
			this.notificationsInterval = setInterval(
				() => this.getActivity(),
				this.communicationsService.notificationsInterval * 1000
			);
		}
	}

	ngOnChanges(changes: SimpleChanges): void {

		this.auctionId = changes.auctionId.currentValue;

		this.getActivity();
	}

	ngOnDestroy(): void {
		this.time_left = null;
		clearInterval(this.countdownInterval);
		clearInterval(this.notificationsInterval);
	}

	ngAfterViewInit(): void {
	}

	getActivity() {

		if (this.auctionId) {

			this.auctionsService.getAuctionActivity(this.auctionId)
			.subscribe((data: BaseResponse<Auction>) => {

				this.utils.logResponse(data.response);

				if (data.code == 200) {
					this.auction = data.response;

					this.images = this.auction.images;

					this.time_left = null;
					clearInterval(this.countdownInterval);

					this.countdown();

					this.countdownInterval = setInterval(() => this.countdown(), 1000);
				}

			}, (data: ErrorResponse) => {
				this.utils.showToast(data.error.messages, 'error');
				this.close.emit();
			});
		}
	}

	convertDate(value: string): string {
		return this.utils.convertDate(value);
	}

	markAllAsSeen() {
		this.communicationsService.markAllAsSeen('bids', this.auctionId);

		this.auction.notifications = this.auction.notifications.map((bid: any) => {
			bid.status = 1;
			return bid;
		});
	}

	bidStyles(notif: Notification) {
		if (notif.is_best_bid) { return 'pill-cyan fa fa-trophy'; }
		else { return 'pill-red fa fa-gavel'; }
	}

	countdown() {

		this.timeLeftLabel = 'Tiempo restante';
		this.lastBidLabel = 'Última puja';

		if (this.auction) {

			if (this.auction.auction_status_id == AuctionStatus.ONGOING) {

				this.time_left = this.utils.timeDiffString(`${this.auction.end_date}`);
			}

			this.timeLeftLabel = this.time_left ? 'Tiempo restante' : 'Subasta finalizada';
			this.lastBidLabel = this.time_left ? 'Última puja' : 'Puja ganadora';

			if (!this.time_left) {
				clearInterval(this.countdownInterval);
			}
		}
	}
}
