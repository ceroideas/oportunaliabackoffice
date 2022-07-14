import { Component, OnInit, Inject } from '@angular/core';
import { Router } from '@angular/router';
import { AppComponent } from 'src/app/app.component';

import { CommunicationsService } from 'src/app/core/services/communications.service';

import { User } from 'src/app/shared/models/user.model';

@Component({
	selector: 'app-leftmenu',
	templateUrl: './leftmenu.component.html',
	styleUrls: ['./leftmenu.component.scss']
})
export class LeftmenuComponent implements OnInit {

	public isCollapsedCommunications = true;
	public Pagecollapse = true;
	public firstname;

	constructor(
		private router: Router,
		public communicationsService: CommunicationsService,
		@Inject(AppComponent) private app: AppComponent
	) {
		if (sessionStorage.getItem('token') == ''
			|| !sessionStorage.getItem('token')
			|| sessionStorage.getItem('email') == ''
			|| !sessionStorage.getItem('email')
		){
			this.router.navigateByUrl('/login');
		} else {
			this.firstname = sessionStorage.getItem('firstname');
		}

		if ((this.router.url).includes('newsletter')) {
			this.isCollapsedCommunications = false;
		}
	}

	ngOnInit(): void {

		// Notifications interval

		this.communicationsService.getNotifications();

		if (this.communicationsService.notificationsOn
			&& !this.communicationsService.notificationsIntervalObject) {

			this.communicationsService.notificationsIntervalObject = setInterval(
				() => this.communicationsService.getNotifications(),
				this.communicationsService.notificationsInterval * 1000
			);
		}
	}

	ngAfterViewInit() {

		setTimeout(() => {

			if (this.router.url.includes('cryptocurrency')) {
				this.app.themeColor('o');
			}
			else if (this.router.url.includes('campaign')) {
				this.app.themeColor('b');
			}
			else if (this.router.url.includes('ecommerce')) {
				this.app.themeColor('a');
			}
			else {
				this.app.themeColor('c');
			}
			const className = document.getElementById('left-sidebar');
			let colorClassName = document.getElementsByClassName('theme-bg');
			if (sessionStorage.getItem('Sidebar') != '' && sessionStorage.getItem('Sidebar') != null) {
				className.classList.add(sessionStorage.getItem('Sidebar'));
			}
			for (let index = 0; index < colorClassName.length; index++) {
				const element = colorClassName[index];
				if (sessionStorage.getItem('GradientColor') != '' && sessionStorage.getItem('GradientColor') != null) {
					element.classList.add('gradient');
				}
				else {
					element.classList.remove('gradient');
				}
			}
		});
	}

	showDropDown() {
		document.getElementById('drp').classList.toggle('ShowDiv')
	}

	toggleMenu() {
		const body = document.getElementsByTagName('body')[0];

		if (body.classList.contains('toggle_menu_active')) {
			body.classList.remove('toggle_menu_active');
		}
		else {
			body.classList.add('toggle_menu_active');
		}
	}

	cToggoleMenu() {
		const body = document.getElementsByTagName('body')[0].classList.remove('offcanvas-active');
		document.getElementsByClassName('overlay')[0].classList.toggle('open');
	}

	logout(){
		sessionStorage.clear();
		this.router.navigateByUrl('/login');
	}
}
