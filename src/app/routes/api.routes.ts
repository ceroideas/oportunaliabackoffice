import { Routes } from '@angular/router';

import { environment } from 'src/environments/environment';

/* Components being used */

import { AssetsComponent } from '../modules/client/assets/assets.component';
import { AssetCategoriesComponent } from '../modules/client/asset-categories/asset-categories.component';
import { AuctionCreateComponent } from '../modules/client/auctions/auction-create/auction-create.component';
import { AuctionsComponent } from '../modules/client/auctions/auctions.component';
import { AuctionDepositsComponent } from '../modules/client/auctions/auction-deposits/auction-deposits.component';
import { AuctionEditComponent } from '../modules/client/auctions/auction-edit/auction-edit.component';
import { AuctionUsersComponent } from '../modules/client/auctions/auction-users/auction-users.component';
import { BlogComponent } from '../modules/client/blog/blog.component';
import { BlogEditComponent } from '../modules/client/blog/blog-edit/blog-edit.component';
import { DepositsComponent } from '../modules/client/deposits/deposits.component';

import { DirectSellingCreateComponent } from '../modules/client/direct-sellings/direct-selling-create/direct-selling-create.component';
import { DirectSellingsComponent } from '../modules/client/direct-sellings/direct-sellings.component';
import { DirectSellingEditComponent } from '../modules/client/direct-sellings/direct-selling-edit/direct-selling-edit.component';
import { DirectSellingUsersComponent } from '../modules/client/direct-sellings/direct-selling-users/direct-selling-users.component';

import { CesionCreateComponent } from '../modules/client/cesions/cesion-create/cesion-create.component';
import { CesionsComponent } from '../modules/client/cesions/cesions.component';
import { CesionEditComponent } from '../modules/client/cesions/cesion-edit/cesion-edit.component';
import { CesionUsersComponent } from '../modules/client/cesions/cesion-users/cesion-users.component';

import { NewsletterEditComponent } from '../modules/client/newsletters/newsletter-edit/newsletter-edit.component';
import { NewslettersComponent } from '../modules/client/newsletters/newsletters.component';
import { NewsletterTemplatesComponent } from '../modules/client/newsletter-templates/newsletter-templates.component';
import { NotificationsCenterComponent } from '../modules/client/notifications-center/notifications-center.component';
import { RepresentationsComponent } from '../modules/client/representations/representations.component';
import { UserComponent } from '../modules/client/user/user.component';
import { UserAuctionsComponent } from '../modules/client/user/user-auctions/user-auctions.component';
import { UserCreateComponent } from '../modules/client/user/user-create/user-create.component';
import { UserDepositsComponent } from '../modules/client/user/user-deposits/user-deposits.component';

import { UserDirectSellingsComponent } from '../modules/client/user/user-direct-sellings/user-direct-sellings.component';
import { UserCesionsComponent } from '../modules/client/user/user-cesions/user-cesions.component';

import { UserEditComponent } from '../modules/client/user/user-edit/user-edit.component';
import { UserRepresentationsComponent } from '../modules/client/user/user-representations/user-representations.component';
import { MembresiaComponent } from '../modules/client/membresia/membresia.component';
import { MembresiaCreateComponent } from '../modules/client/membresia-create/membresia-create.component';
import { NotFoundComponent } from '../modules/client/not-found/not-found.component';

export const apiRoutes: Routes = [

	// Users

	{
		path: 'users',
		component: UserComponent,
		data: { title: 'Oportunalia | Usuarios' },
	},
	{
		path: 'users/create',
		component: UserCreateComponent,
		data: { title: 'Oportunalia | Nuevo Usuario' }
	},
	{
		path: 'users/:id/edit',
		component: UserEditComponent,
		data: { title: 'Oportunalia | Editar Usuario' }
	},
	{
		path: 'users/:id/auctions',
		component: UserAuctionsComponent,
		data: { title: 'Oportunalia | Subastas de Usuario' }
	},
	{
		path: 'users/:id/deposits',
		component: UserDepositsComponent,
		data: { title: 'Oportunalia | Depósitos de Usuario' }
	},
	{
		path: 'users/:id/direct-sellings',
		component: UserDirectSellingsComponent,
		data: { title: 'Oportunalia | Ventas Directas de Usuario' }
	},
  {
		path: 'users/:id/cesions',
		component: UserCesionsComponent,
		data: { title: 'Oportunalia | Cesiones de remate de Usuario' }
	},
	{
		path: 'users/:id/representations',
		component: UserRepresentationsComponent,
		data: { title: 'Oportunalia | Representaciones de Usuario' }
	},

	{
		path:'deposits',
		component: DepositsComponent,
		data: { title: 'Oportunalia | Depósitos' }
	},
	{
		path:'representations',
		component: RepresentationsComponent,
		data: { title: 'Oportunalia | Representaciones' }
	},

	// Assets

	{
		path: 'assets',
		component: AssetsComponent,
		data: { title: 'Oportunalia | Activos' }
	},
	{
		path: 'asset-categories',
		component: AssetCategoriesComponent,
		data: { title: 'Oportunalia | Categorías de Activo' }
	},

	// Auctions

	{
		path:'auctions',
		component: AuctionsComponent,
		data: { title: 'Oportunalia | Subastas' }
	},
	{
		path:'auctions/create',
		component: AuctionCreateComponent,
		data: { title: 'Oportunalia | Subastas' }
	},
	{
		path:'auctions/:id/edit',
		component: AuctionEditComponent,
		data: { title: 'Oportunalia | Subastas' }
	},
	{
		path:'auctions/:id/users',
		component: AuctionUsersComponent,
		data: { title: 'Oportunalia | Subastas' }
	},
	{
		path:'auctions/:id/deposits',
		component: AuctionDepositsComponent,
		data: { title: 'Oportunalia | Subastas' }
	},

	// Direct Sellings

	{
		path:'direct-sellings',
		component: DirectSellingsComponent,
		data: { title: 'Oportunalia | Venta Directa' }
	},
	{
		path:'direct-sellings/create',
		component: DirectSellingCreateComponent,
		data: { title: 'Oportunalia | Venta Directa' }
	},
	{
		path:'direct-sellings/:id/edit',
		component: DirectSellingEditComponent,
		data: { title: 'Oportunalia | Venta Directa' }
	},
	{
		path:'direct-sellings/:id/users',
		component: DirectSellingUsersComponent,
		data: { title: 'Oportunalia | Venta Directa' }
	},

  	// Cesions

	{
		path:'cesions',
		component: CesionsComponent,
		data: { title: 'Oportunalia | Cesión de remate' }
	},
	{
		path:'cesions/create',
		component: CesionCreateComponent,
		data: { title: 'Oportunalia | Cesión de remate' }
	},
	{
		path:'cesions/:id/edit',
		component: CesionEditComponent,
		data: { title: 'Oportunalia | Cesión de remate' }
	},
	{
		path:'cesions/:id/users',
		component: CesionUsersComponent,
		data: { title: 'Oportunalia | Cesión de remate' }
	},
	// Communications

	{
		path: 'newsletters',
		component: NewslettersComponent,
		data: { title: 'Oportunalia | Newsletters' }
	},
	{
		path: 'newsletters/create',
		component: NewsletterEditComponent,
		data: { title: 'Oportunalia | Newsletters' }
	},
	{
		path: 'newsletters/:id/edit',
		component: NewsletterEditComponent,
		data: { title: 'Oportunalia | Newsletters' }
	},
	{
		path: 'newsletter-templates',
		component: NewsletterTemplatesComponent,
		data: { title: 'Oportunalia | Plantillas de Newsletters' }
	},
	{
		path: 'blog',
		component: BlogComponent,
		data: { title: 'Oportunalia | Blog' }
	},
	{
		path: 'blog/create',
		component: BlogEditComponent,
		data: { title: 'Oportunalia | Blog' }
	},
	{
		path: 'blog/:id/edit',
		component: BlogEditComponent,
		data: { title: 'Oportunalia | Blog' }
	},
	{
		path: 'membresia',
		component: MembresiaComponent,
		data: { title: 'Oportunalia | Membresía' }
	},
	{
		path: 'membresia/create',
		component: MembresiaCreateComponent,
		data: { title: 'Oportunalia | Membresía' }
	},
	{
		path: 'notifications-center',
		component: NotificationsCenterComponent,
		data: { title: 'Oportunalia | Centro de Notificaciones' }
	},

	// Various

	{
		path: '', pathMatch: 'full', redirectTo: environment.defaultRoute
	},
	{
		path: '**',
		component: NotFoundComponent,
		data: { title: 'Oportunalia | Página no encontrada' }
	},
];
