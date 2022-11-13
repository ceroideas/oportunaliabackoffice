import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { LoginComponent } from './login/login.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';

const routes: Routes = [
	{
		path: 'login',
		component: LoginComponent,
		data: { title: 'Oportunalia | Entrar' }
	},
	{
		path: 'forgot-password',
		component: ForgotPasswordComponent,
		data: { title: 'Oportunalia | Reestablecer Contraseña' }
	}
];

@NgModule({
	imports: [RouterModule.forChild(routes)],
	exports: [RouterModule],
})
export class AuthRoutingModule {
	static components = [
		LoginComponent,
		ForgotPasswordComponent
	];
}
