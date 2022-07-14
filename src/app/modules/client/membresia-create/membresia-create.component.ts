import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { HttpClient, HttpHeaders } from '@angular/common/http';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Membresia } from 'src/app/shared/models/communication.model';

import { formErrors } from 'src/app/shared/validators';
import { AssetCategory } from 'src/app/shared/models/asset.model';

import { DataService } from 'src/app/core/services/data.service';
import { User } from 'src/app/shared/models/user.model';

declare var $:JQueryStatic;

@Component({
  selector: 'app-membresia-create',
  templateUrl: './membresia-create.component.html',
  styleUrls: ['./membresia-create.component.scss']
})
export class MembresiaCreateComponent implements OnInit {

	// Form

	public membresia: Membresia = {} as any;

  	public users: any[];
  	public auctions: any[];

	public searchUser = "";
	public searchAuction = "";

	public form: FormGroup;
	public submitted: boolean = false;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }

	public editor = ClassicEditor;
	public ckOptions = null;

  	constructor(
    	private router: Router,
		private route: ActivatedRoute,
		public fb: FormBuilder,
		public utils: UtilsService,
		public communicationsService: CommunicationsService,
		public http: HttpClient,
		public dataService: DataService,
  	) {}

	ngOnInit(): void {

		this.ckOptions = this.utils.ckOptions;
    
		this.setFormFields();

		this.dataService.getMembresiaUsers()
		.then((val) => {
			$('#users_select').append(`<option selected="selected" disabled>Selecciona un usuario</option>`);
			let users = val['response'];
			this.users = val['response']
			for (let i = 0; i < users.length ; i++){
				$('#users_select').append($('<option>', {
					value: users[i].id,
					text: users[i].username
				}));
			}
		});
		
		this.dataService.getMembresiaAuctions()
		.then((val) => {
			$('#auctions_select').append(`<option selected="selected" disabled>Selecciona una subasta</option>`);
			let auctions = val['response'];
			this.auctions = val['response']
			for (let i = 0; i < auctions.length ; i++){
				$('#auctions_select').append($('<option>', {
					value: auctions[i].id,
					text: auctions[i].title
				}));
			}
		});
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			note: ['', [Validators.required]],
			auction_id: ['', [Validators.required]],
			user_id: ['', [Validators.required]],
		});
	}

	saveMembresia() {

		this.submitted = true;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.membresia);

			const data = new FormData();

			data.append('note', this.form.get('note').value);
			data.append('auction_id', this.form.get('auction_id').value);
			data.append('user_id', this.form.get('user_id').value);

			this.communicationsService.saveMembresia(data)
			  .subscribe(data => {
					this.router.navigate(['/membresia']);
					this.utils.showToast('Añadido correctamente');
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
			  });


		} else {
			this.utils.showToast('Formulario incorrecto', 'error');
		}
	}


	userSearch(){
		let search_text = ""+$("#users_search").val();
		let filtered = [];
		filtered = this.users.filter(function(user) {
			return user.username.toLowerCase().includes(search_text.toLowerCase())
		});
		$('#users_select').find('option').remove().end();
		$('#users_select').append(`<option selected="selected" disabled>Selecciona un usuario</option>`);
		for (let i = 0; i < filtered.length ; i++){
			$('#users_select').append($('<option>', {
				value: filtered[i].id,
				text: filtered[i].username
			}));
		}	
	}


	auctionSearch(){
		let search_text = ""+$("#auctions_search").val();
		let filtered = [];
		filtered = this.auctions.filter(function(auction) {
			return auction.title.toLowerCase().includes(search_text.toLowerCase())
		});
		$('#auctions_select').find('option').remove().end();
		$('#auctions_select').append(`<option selected="selected" disabled>Selecciona una subasta</option>`);
		for (let i = 0; i < filtered.length ; i++){
			$('#auctions_select').append($('<option>', {
				value: filtered[i].id,
				text: filtered[i].title
			}));
		}
	}

}
