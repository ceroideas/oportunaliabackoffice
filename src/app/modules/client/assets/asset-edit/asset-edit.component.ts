import { Component, OnInit, OnChanges, SimpleChanges, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { HttpClient } from '@angular/common/http';

import { UtilsService } from 'src/app/core/services/utils.service';
import { DataService } from 'src/app/core/services/data.service';
import { AssetsService } from 'src/app/core/services/assets.service';

import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';
import { Province } from 'src/app/shared/models/data.model';
import { AuctionStatus } from 'src/app/shared/models/auction.model';
import { Asset, AssetCategory, Condition } from 'src/app/shared/models/asset.model';

import { formErrors, minLengthArray } from 'src/app/shared/validators';

declare var google:any;

@Component({
	selector: 'app-asset-edit',
	templateUrl: './asset-edit.component.html',
	styleUrls: ['./asset-edit.component.scss']
})
export class AssetEditComponent implements OnInit, OnChanges {

	@Input() assetId!: number;
	@Output() success: EventEmitter<any> = new EventEmitter();
	@Output() close: EventEmitter<any> = new EventEmitter();

	// Selectors

	public provinces: Province[] = [];
	public categories: AssetCategory[] = [];
	public conditions: Condition[] = [];

	// Modals

	public modalTitle = '';

	// Modal form

	public asset: Asset = {} as any;
	public form: FormGroup;
	public submitted: boolean = false;
	public disabled: boolean = true;
	errors(key, type) { return formErrors(this.form, this.submitted, key, type); }
	createForArray(data): FormGroup { return this.fb.group(data); }
	get imagesArray(): FormArray {
		return this.form.get('images') as FormArray;
	}
	public imageGallery: Array<any> = [];
	public imagesDeleteArray: Array<any> = [];

	private MAX_FILE_SIZE: number = 8000000;

	constructor(
		private _http: HttpClient,
		public fb: FormBuilder,
		public utils: UtilsService,
		public dataService: DataService,
		public assetsService: AssetsService
	) {
	}

	ngOnInit(): void {

		this.dataService.getProvinces().then((val: Province) => {
			this.provinces = val['response'];
		});

		this.dataService.getAssetCategories()
		.then((val: AssetCategory) => {
			this.categories = val['response'];
		});

		this.dataService.getConditions()
		.then((val: Condition) => {
			this.conditions = val['response'];
		});
	}

	ngOnChanges(changes: SimpleChanges): void {

		this.assetId = changes.assetId.currentValue;

		this.disabled = false;

		this.setFormFields();

		if (this.assetId) {

			this.modalTitle = 'Editar Activo';

			this.assetsService.getAsset(this.assetId)
			.subscribe((data: BaseResponse<Asset>) => {

				this.utils.logResponse(data.response);

				if (data.code == 200) {
					this.asset = data.response;

					if (this.asset.auction_id != null &&
						![AuctionStatus.DRAFT, AuctionStatus.SOON, AuctionStatus.ONGOING].includes(this.asset.auction_status_id)
					) {
						// this.disabled = true;
					}

					this.form.patchValue(this.asset);

					let lat = parseFloat(this.form.get('lat').value);
					let lng = parseFloat(this.form.get('lng').value);

					this.form.patchValue({'lat':lat});
			        this.form.patchValue({'lng':lng});

			        this.map.setCenter({lat:lat,lng:lng});
			        this.marker.setPosition({lat:lat,lng:lng});

					this.imagesArray.clear();

					this.asset.images.forEach((image: any) => {
						this.imagesArray.push(this.createForArray({
							file: null,
							id: image.id,
							url: image.path,
							name: image.name,
						}));

						this.imageGallery.push({
							url: image.path,
							name: image.name,
						});
					});
				}

			}, (data: ErrorResponse) => {
				this.utils.showToast(data.error.messages, 'error');
				this.close.emit();
			});

		} else {

			this.modalTitle = 'Nuevo Activo';
		}
	}

	ngOnDestroy(): void {
	}

	ngAfterViewInit(): void {
		this.initMap();
	}

	setFormFields() {

		this.submitted = false;

		this.form = this.fb.group({
			id: [''],
			name: ['', [Validators.required]],
			active_category_id: ['', [Validators.required]],
			address: ['', [Validators.required]],
			city: ['', [Validators.required]],
			province_id: ['', [Validators.required]],
			lat: ['', [Validators.required]],
			lng: ['', [Validators.required]],
			refund: [''],
			requires_academy_access: [false],
			active_condition_id: ['', [Validators.required]],
			area: ['', [Validators.required]],
			images: this.fb.array([]),
		});
	}

	uploadImages(event: any) {

		if (event.target.files) {

			for (let file of event.target.files) {
				const reader = new FileReader();

				if (file.size > this.MAX_FILE_SIZE) {
					this.utils.showToast(`Un archivo supera los 8MB  ${file.name})`, 'error');
					return;
				}

				reader.onload = (evt: any) => {
					this.imagesArray.push(this.createForArray({
						file,
						id: null,
						url: evt.target.result,
						name: file.name
					}));

					this.imageGallery.push({
						url: evt.target.result,
						name: file.name
					});
				};

				reader.readAsDataURL(file);
			}
		}
	}

	removeImage(id: number) {

		let image = this.imagesArray.at(id).value;
		if (image.id) {
			this.imagesDeleteArray.push(image.id);
		}
		this.imagesArray.removeAt(id);
		this.imageGallery.splice(id, 1);
	}

	saveAsset() {

		this.submitted = true;
		let assetId = this.form.get('id').value;

		if (this.form.valid) {

			this.utils.formToObject(this.form, this.asset);

			const data = new FormData();

			data.append('name', this.form.get('name').value);
			data.append('active_category_id', this.form.get('active_category_id').value);
			data.append('address', this.form.get('address').value);
			data.append('city', this.form.get('city').value);
			data.append('province_id', this.form.get('province_id').value);
			data.append('refund', this.form.get('refund').value ? '1' : '0');
			data.append('requires_academy_access', this.form.get('requires_academy_access').value ? '1' : '0');
			data.append('lat', this.form.get('lat').value);
			data.append('lng', this.form.get('lng').value);
			data.append('active_condition_id', this.form.get('active_condition_id').value);
			data.append('area', this.form.get('area').value);

			this.imagesArray.value.forEach(value => {
				if (value.file) {
					data.append('images[]', value.file);
				}
			});

			// Call for image deletion, no confirmation needed
			this.imagesDeleteArray.forEach(id => {
				this.assetsService.deleteAssetImage(id).subscribe();
			});

			if (!assetId) {

				this.assetsService.saveAsset(data)
				.subscribe(data => {
					this.utils.showToast('Creado correctamente');
					this.success.emit();
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});

			} else {

				data.append('id', assetId);

				this.assetsService.editAsset(data, assetId)
				.subscribe(data => {
					this.utils.showToast('Editado correctamente');
					this.success.emit();
				}, data => {
					if (data.error.code == 401) {
						this.utils.parseResponseErrors(this.form, data);
						this.utils.showToast('Formulario incorrecto', 'error');
					} else {
						this.utils.showToast(data.error.messages, 'error');
					}
				});
			}

		} else {
			this.utils.showToast('Formulario incorrecto', 'error');
		}
	}

	openMaps() {

		let query = '';

		if (this.form.get('address').value) {
			query += this.form.get('address').value.trim()+',';
		}

		if (this.form.get('city').value) {
			query += this.form.get('city').value.trim()+',';
		}

		if (this.form.get('province_id').value) {

			this.provinces.forEach(province => {

				if (province.id == this.form.get('province_id').value) {
					query += province.name;
				}
			});
		}

		// this.assetsService.openMaps(query);
		this.geocodeAddress(query);
	}

	map:any;
	marker:any;

	initMap() {
	  var initialPosition = { 
	  	lat: 41.0546247,
	  	lng: -4.806823
	  };

	  console.log(initialPosition);

	  // Crea el mapa
	  this.map = new google.maps.Map(document.getElementById("map"), {
	    zoom: 8,
	    center: initialPosition,
	  });

	  // Crea el marcador
	  this.marker = new google.maps.Marker({
	    position: initialPosition,
	    map: this.map,
	    draggable: true, // Permitir arrastrar el marcador
	  });

	  // Escucha cambios en la posición del marcador
	  google.maps.event.addListener(this.marker, "dragend", (event) => {
		this.form.patchValue({'lat':event.latLng.lat()});
		this.form.patchValue({'lng':event.latLng.lng()});
	  });

	  // Permitir mover el marcador con un clic en el mapa
	  google.maps.event.addListener(this.map, "click", (event) => {
	    const clickedPosition = event.latLng;
	    this.marker.setPosition(clickedPosition);
	    console.log("Clic en el mapa:", clickedPosition.lat(), clickedPosition.lng());
	  });
	}

	private apiKey = 'AIzaSyDsj-gbtqTAsxtWNbcqrRmE8ExatChS_Ko';
	private apiUrl = 'https://maps.googleapis.com/maps/api/geocode/json';

	geocodeAddress(address: string): void {
	    this.getCoordinates(address).subscribe(response => {
	      if (response.status === 'OK') {
	        const location = response.results[0].geometry.location;

	        this.form.patchValue({'lat':location.lat});
	        this.form.patchValue({'lng':location.lng});

	        this.map.setCenter({lat:location.lat,lng:location.lng});
	        this.marker.setPosition({lat:location.lat,lng:location.lng});

	      } else {
	        console.error('Geocoding error:', response.status);
	      }
	    });
	}

	getCoordinates(address: string): any {
	    const url = `${this.apiUrl}?address=${encodeURIComponent(address)}&key=${this.apiKey}`;
	    return this._http.get(url);
	}
}
