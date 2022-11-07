import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { MatSliderModule } from '@angular/material/slider';

// ngx

// import { AccordionModule } from 'ngx-bootstrap/accordion';
// import { AlertModule } from 'ngx-bootstrap/alert';
// import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { BsDropdownModule } from 'ngx-bootstrap/dropdown';
import { CollapseModule } from 'ngx-bootstrap/collapse';
import { ModalModule } from 'ngx-bootstrap/modal';
import { NgxDropzoneModule } from 'ngx-dropzone';
import { PopoverModule } from 'ngx-bootstrap/popover';
import { ProgressbarModule } from 'ngx-bootstrap/progressbar';
import { TimepickerModule } from 'ngx-bootstrap/timepicker';
import { ToastrModule } from 'ngx-toastr';
import { TooltipModule } from 'ngx-bootstrap/tooltip';

// API routes

import { ClientRoutingModule } from './client-routing.module';

import { HeaderComponent } from './header/header.component';
import { LeftmenuComponent } from './leftmenu/leftmenu.component';

import { AssetEditComponent } from './assets/asset-edit/asset-edit.component';
import { AssetsComponent } from './assets/assets.component';
import { AssetCategoriesComponent } from './asset-categories/asset-categories.component';
import { AuctionCreateComponent } from './auctions/auction-create/auction-create.component';
import { AuctionDepositsComponent } from './auctions/auction-deposits/auction-deposits.component';
import { AuctionEditComponent } from './auctions/auction-edit/auction-edit.component';
import { AuctionHistoryComponent } from './auctions/auction-history/auction-history.component';
import { AuctionUsersComponent } from './auctions/auction-users/auction-users.component';
import { AuctionsComponent } from './auctions/auctions.component';
import { BlogComponent } from './blog/blog.component';
import { BlogEditComponent } from './blog/blog-edit/blog-edit.component';
import { DepositsComponent } from './deposits/deposits.component';
import { DirectSellingCreateComponent } from './direct-sellings/direct-selling-create/direct-selling-create.component';
import { DirectSellingEditComponent } from './direct-sellings/direct-selling-edit/direct-selling-edit.component';
import { DirectSellingHistoryComponent } from './direct-sellings/direct-selling-history/direct-selling-history.component';
import { DirectSellingUsersComponent } from './direct-sellings/direct-selling-users/direct-selling-users.component';
import { DirectSellingsComponent } from './direct-sellings/direct-sellings.component';
import { NewsletterAuctionsComponent } from './newsletters/newsletter-auctions/newsletter-auctions.component';
import { NewsletterEditComponent } from './newsletters/newsletter-edit/newsletter-edit.component';
import { NewslettersComponent } from './newsletters/newsletters.component';
import { NewsletterTemplateEditComponent } from './newsletter-templates/newsletter-template-edit/newsletter-template-edit.component';
import { NewsletterTemplatesComponent } from './newsletter-templates/newsletter-templates.component';
import { NotificationBidsComponent } from './notifications/notification-bids/notification-bids.component';
import { NotificationsComponent } from './notifications/notifications.component';
import { NotificationsCenterComponent } from './notifications-center/notifications-center.component';
import { RepresentationsComponent } from './representations/representations.component';
import { RepresentationEditComponent } from './representations/representation-edit/representation-edit.component';
import { UserComponent } from './user/user.component';
import { UserAuctionsComponent } from './user/user-auctions/user-auctions.component';
import { UserCreateComponent } from './user/user-create/user-create.component';
import { UserDepositsComponent } from './user/user-deposits/user-deposits.component';
import { UserDirectSellingsComponent } from './user/user-direct-sellings/user-direct-sellings.component';
import { UserEditComponent } from './user/user-edit/user-edit.component';
import { UserRepresentationsComponent } from './user/user-representations/user-representations.component';
import { NotFoundComponent } from './not-found/not-found.component';
import { MembresiaComponent } from './membresia/membresia.component';
import { MembresiaCreateComponent } from './membresia-create/membresia-create.component';

/* Components not being used */

// import { ChartsComponent } from './charts/charts.component';
// import { WorldmapComponent } from './worldmap/worldmap.component';

// Pages

// import { PagesComponent } from './pages/pages.component';
// import { PricingComponent } from './pages/pricing/pricing.component';
// import { TimelineComponent } from './pages/timeline/timeline.component';
// import { ImageGalleryComponent } from './pages/image-gallery/image-gallery.component';
// import { InvoicesComponent } from './pages/invoices/invoices.component';
// import { InvoiceDetailComponent } from './pages/invoices/invoice-detail/invoice-detail.component';
// import { ProfileComponent } from './pages/profile/profile.component';
// import { SearchResultsComponent } from './pages/search-results/search-results.component';
// import { BlankPageComponent } from './pages/blank-page/blank-page.component';
// import { PageNotFoundComponent } from './pages/page-not-found/page-not-found.component';

// External: NgApex Charts

// import { NgApexchartsModule } from 'ng-apexcharts';

// import { AreaChartComponent } from './charts/area-chart/area-chart.component';
// import { BarChartComponent } from './charts/bar-chart/bar-chart.component';
// import { CandlestickChartComponent } from './charts/candlestick-chart/candlestick-chart.component';
// import { ColumnChartComponent } from './charts/column-chart/column-chart.component';
// import { HeatmapChartComponent } from './charts/heatmap-chart/heatmap-chart.component';
// import { LineChartComponent } from './charts/line-chart/line-chart.component';
// import { MinbarchartComponent } from './charts/minbarchart/minbarchart.component';
// import { PieChartComponent } from './charts/pie-chart/pie-chart.component';
// import { RadarChartComponent } from './charts/radar-chart/radar-chart.component';
// import { RadialbarChartComponent } from './charts/radialbar-chart/radialbar-chart.component';
// import { SparklinesComponent } from './charts/sparklines/sparklines.component';

// External: calendars

import { CalendarModule, DateAdapter } from 'angular-calendar';
import { adapterFactory } from 'angular-calendar/date-adapters/date-fns';
import { FullCalendarModule } from '@fullcalendar/angular'; // the main connector. must go first
import dayGridPlugin from '@fullcalendar/daygrid'; // a plugin
import interactionPlugin from '@fullcalendar/interaction'; // a plugin
import timeGridPlugin from '@fullcalendar/timegrid';

import { AgmCoreModule } from '@agm/core';
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';
import { DataTablesModule } from 'angular-datatables';
import { CesionsComponent } from './cesions/cesions.component';
// import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';

// UI showcase

// import { UiElementComponent } from './ui-element/ui-element.component';
// import { UiHelperComponent } from './ui-element/ui-helper/ui-helper.component';
// import { UiBootstrapComponent } from './ui-element/ui-bootstrap/ui-bootstrap.component';
// import { UiTypographyComponent } from './ui-element/ui-typography/ui-typography.component';
// import { UiTabsComponent } from './ui-element/ui-tabs/ui-tabs.component';
// import { UiButtonsComponent } from './ui-element/ui-buttons/ui-buttons.component';
// import { UiIconsComponent } from './ui-element/ui-icons/ui-icons.component';
// import { UiNotificationsComponent } from './ui-element/ui-notifications/ui-notifications.component';
// import { UiColorsComponent } from './ui-element/ui-colors/ui-colors.component';
// import { UiDialogsComponent } from './ui-element/ui-dialogs/ui-dialogs.component';
// import { UiListGroupComponent } from './ui-element/ui-list-group/ui-list-group.component';
// import { UiMediaObjectComponent } from './ui-element/ui-media-object/ui-media-object.component';
// import { UiModalsComponent } from './ui-element/ui-modals/ui-modals.component';
// import { UiNestableComponent } from './ui-element/ui-nestable/ui-nestable.component';
// import { UiProgressBarsComponent } from './ui-element/ui-progress-bars/ui-progress-bars.component';
// import { UiRangeSlidersComponent } from './ui-element/ui-range-sliders/ui-range-sliders.component';

FullCalendarModule.registerPlugins([ // register FullCalendar plugins
	dayGridPlugin,
	timeGridPlugin,
	interactionPlugin
]);

@NgModule({
	imports: [
		CommonModule,
		ReactiveFormsModule,
		FormsModule,
		DragDropModule,
		MatSliderModule,

		// AccordionModule.forRoot(),
		// AlertModule.forRoot(),
		// BsDatepickerModule.forRoot(),
		BsDropdownModule.forRoot(),
		CollapseModule.forRoot(),
		ModalModule.forRoot(),
		NgxDropzoneModule,
		PopoverModule.forRoot(),
		ProgressbarModule.forRoot(),
		TimepickerModule.forRoot(),
		ToastrModule.forRoot({}),
		TooltipModule.forRoot(),

		ClientRoutingModule,

		// NgApexchartsModule,

		CalendarModule.forRoot({
			provide: DateAdapter,
			useFactory: adapterFactory,
		}),
		FullCalendarModule,

		AgmCoreModule.forRoot({
			apiKey: 'GOOGLE_API_KEY'
		}),
		CKEditorModule,
		DataTablesModule,
		// NgMultiSelectDropDownModule.forRoot(),
	],
	declarations: [
		ClientRoutingModule.components,

		// Layout

		HeaderComponent,
		LeftmenuComponent,

		// API routes

		AssetEditComponent,
		AssetsComponent,
		AssetCategoriesComponent,
		AuctionCreateComponent,
		AuctionDepositsComponent,
		AuctionEditComponent,
		AuctionHistoryComponent,
		AuctionUsersComponent,
		AuctionsComponent,
		BlogComponent,
		BlogEditComponent,
		DepositsComponent,
		DirectSellingCreateComponent,
		DirectSellingEditComponent,
		DirectSellingHistoryComponent,
		DirectSellingUsersComponent,
		DirectSellingsComponent,
		NewsletterAuctionsComponent,
		NewsletterEditComponent,
		NewslettersComponent,
		NewsletterTemplateEditComponent,
		NewsletterTemplatesComponent,
		NotificationBidsComponent,
		NotificationsComponent,
		NotificationsCenterComponent,
		RepresentationEditComponent,
		RepresentationsComponent,
		UserAuctionsComponent,
		UserComponent,
		UserCreateComponent,
		UserDepositsComponent,
		UserDirectSellingsComponent,
		UserEditComponent,
		UserRepresentationsComponent,
		MembresiaComponent,
		MembresiaCreateComponent,
		
		// Various

		NotFoundComponent,
		
		CesionsComponent,

		// Not used

		// ChartsComponent,
		// WorldmapComponent,

		// Charts

		// AreaChartComponent,
		// BarChartComponent,
		// CandlestickChartComponent,
		// ColumnChartComponent,
		// HeatmapChartComponent,
		// LineChartComponent,
		// MinbarchartComponent,
		// PieChartComponent,
		// RadarChartComponent,
		// RadialbarChartComponent,
		// SparklinesComponent,

		// Pages

		// PagesComponent,
		// BlankPageComponent,
		// ImageGalleryComponent,
		// InvoicesComponent,
		// InvoiceDetailComponent,
		// PageNotFoundComponent,
		// PricingComponent,
		// ProfileComponent,
		// SearchResultsComponent,
		// TimelineComponent,

		// UI showcase

		// UiElementComponent,
		// UiHelperComponent,
		// UiBootstrapComponent,
		// UiTypographyComponent,
		// UiTabsComponent,
		// UiButtonsComponent,
		// UiIconsComponent,
		// UiNotificationsComponent,
		// UiColorsComponent,
		// UiDialogsComponent,
		// UiListGroupComponent,
		// UiMediaObjectComponent,
		// UiModalsComponent,
		// UiNestableComponent,
		// UiProgressBarsComponent,
		// UiRangeSlidersComponent,
	],
	providers: [
		// BsDatepickerModule
	]
})
export class ClientModule { }
