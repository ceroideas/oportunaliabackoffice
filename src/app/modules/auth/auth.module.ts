import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { AuthRoutingModule } from './auth-routing.module';
import { LoginComponent } from './login/login.component';
import { ForgotPasswordComponent } from './forgot-password/forgot-password.component';

@NgModule({
  imports: [
    CommonModule,
    AuthRoutingModule,
    ReactiveFormsModule, FormsModule
  ],
  declarations: [AuthRoutingModule.components, LoginComponent, ForgotPasswordComponent],
  providers: []
})
export class AuthModule { }