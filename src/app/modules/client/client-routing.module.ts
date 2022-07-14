import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { apiRoutes } from 'src/app/routes/api.routes';

@NgModule({
	imports: [RouterModule.forChild(apiRoutes)],
	exports: [RouterModule],
})
export class ClientRoutingModule {
	static components = [
	];
}
