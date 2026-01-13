import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { formatDate } from '@angular/common';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';

@Component({
	selector: 'app-academy-payment-detail',
	templateUrl: './academy-payment-detail.component.html',
	styleUrls: ['./academy-payment-detail.component.scss']
})
export class AcademyPaymentDetailComponent implements OnInit {

	public payment: any = null;
	public loading = true;
	public paymentId: number;

	constructor(
		private route: ActivatedRoute,
		private router: Router,
		public utils: UtilsService,
		public dataService: DataService
	) {
	}

	ngOnInit(): void {
		this.route.params.subscribe(params => {
			this.paymentId = params.id;
			this.loadPayment();
		});
	}

	loadPayment(): void {
		this.loading = true;
		this.dataService.http.get(endpoint('academy_payments_get', { id: this.paymentId }), { headers: this.dataService.headers })
			.toPromise()
			.then((response: any) => {
				this.utils.logResponse(response);
				if (response.code === 200) {
					this.payment = response.response;
				} else {
					this.utils.showToast('Error al cargar pago', 'error');
					this.router.navigate(['/academy/payments']);
				}
				this.loading = false;
			})
			.catch((error: any) => {
				console.error(error);
				this.utils.showToast('Error al cargar pago', 'error');
				this.loading = false;
				this.router.navigate(['/academy/payments']);
			});
	}

	formatDate(date: string): string {
		if (!date) return '-';
		return formatDate(date, 'dd/MM/yyyy HH:mm', 'es');
	}

	getStatusBadgeClass(status: string): string {
		switch(status) {
			case 'succeeded': return 'badge-success';
			case 'pending': return 'badge-warning';
			case 'failed': return 'badge-danger';
			case 'refunded': return 'badge-secondary';
			default: return 'badge-secondary';
		}
	}

	getStatusText(status: string): string {
		switch(status) {
			case 'succeeded': return 'Completado';
			case 'pending': return 'Pendiente';
			case 'failed': return 'Fallido';
			case 'refunded': return 'Reembolsado';
			default: return status;
		}
	}

	goBack(): void {
		this.router.navigate(['/academy/payments']);
	}
}

