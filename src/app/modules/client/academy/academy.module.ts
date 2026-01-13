import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

// Academy Components
import { AcademyStudentsComponent } from '../academy-students/academy-students.component';
import { AcademyStudentDetailComponent } from '../academy-students/academy-student-detail/academy-student-detail.component';
import { AcademyStudentEditComponent } from '../academy-students/academy-student-edit/academy-student-edit.component';
import { AcademyStudentCoursesComponent } from '../academy-students/academy-student-courses/academy-student-courses.component';
import { AcademyStudentViewsComponent } from '../academy-students/academy-student-views/academy-student-views.component';
import { AcademyCoursesComponent } from '../academy-courses/academy-courses.component';
import { AcademyCourseDetailComponent } from '../academy-courses/academy-course-detail/academy-course-detail.component';
import { AcademyCourseEditComponent } from '../academy-courses/academy-course-edit/academy-course-edit.component';
import { AcademyPaymentsComponent } from '../academy-payments/academy-payments.component';
import { AcademyPaymentDetailComponent } from '../academy-payments/academy-payment-detail/academy-payment-detail.component';

// External modules
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';
import { DataTablesModule } from 'angular-datatables';
import { ModalModule } from 'ngx-bootstrap/modal';
import { ToastrModule } from 'ngx-toastr';

/**
 * Academy Module
 * 
 * Para desactivar el módulo de Academia:
 * 1. Comenta todas las importaciones de Academy en client.module.ts
 * 2. Comenta la importación y rutas de academyRoutes en api.routes.ts
 * 3. Opcionalmente, elimina o comenta este archivo
 */
@NgModule({
	imports: [
		CommonModule,
		ReactiveFormsModule,
		FormsModule,
		CKEditorModule,
		DataTablesModule,
		ModalModule.forRoot(),
		ToastrModule.forRoot({}),
	],
	declarations: [
		AcademyStudentsComponent,
		AcademyStudentDetailComponent,
		AcademyStudentEditComponent,
		AcademyStudentCoursesComponent,
		AcademyStudentViewsComponent,
		AcademyCoursesComponent,
		AcademyCourseDetailComponent,
		AcademyCourseEditComponent,
		AcademyPaymentsComponent,
		AcademyPaymentDetailComponent,
	],
	exports: [
		AcademyStudentsComponent,
		AcademyStudentDetailComponent,
		AcademyStudentEditComponent,
		AcademyStudentCoursesComponent,
		AcademyStudentViewsComponent,
		AcademyCoursesComponent,
		AcademyCourseDetailComponent,
		AcademyCourseEditComponent,
		AcademyPaymentsComponent,
		AcademyPaymentDetailComponent,
	]
})
export class AcademyModule { }

