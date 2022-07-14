import { NgModule, LOCALE_ID } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { registerLocaleData } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { ToastrModule } from 'ngx-toastr';
// import { DatepickerModule, BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { DataTablesModule } from "angular-datatables";
// import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

import localeEs from '@angular/common/locales/es';

registerLocaleData(localeEs, 'es');

@NgModule({
	declarations: [
		AppComponent,
	],
	imports: [
		BrowserModule,
		AppRoutingModule,
		HttpClientModule,
		ReactiveFormsModule,
		FormsModule,
		DataTablesModule,
		BrowserAnimationsModule,
		// BsDatepickerModule.forRoot(),
		// NgMultiSelectDropDownModule.forRoot(),
		ToastrModule.forRoot({
			positionClass: 'toast-top-center',
		})
	],
	providers: [
		// BsDatepickerModule,
		{ provide: LOCALE_ID, useValue: 'es' }
	],
	bootstrap: [AppComponent]
})
export class AppModule { }
