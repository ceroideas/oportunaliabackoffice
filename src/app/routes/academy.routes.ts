import { Routes } from '@angular/router';

// Academy Components
import { AcademyStudentsComponent } from '../modules/client/academy-students/academy-students.component';
import { AcademyStudentDetailComponent } from '../modules/client/academy-students/academy-student-detail/academy-student-detail.component';
import { AcademyStudentEditComponent } from '../modules/client/academy-students/academy-student-edit/academy-student-edit.component';
import { AcademyStudentCoursesComponent } from '../modules/client/academy-students/academy-student-courses/academy-student-courses.component';
import { AcademyStudentViewsComponent } from '../modules/client/academy-students/academy-student-views/academy-student-views.component';
import { AcademyCoursesComponent } from '../modules/client/academy-courses/academy-courses.component';
import { AcademyCourseDetailComponent } from '../modules/client/academy-courses/academy-course-detail/academy-course-detail.component';
import { AcademyCourseEditComponent } from '../modules/client/academy-courses/academy-course-edit/academy-course-edit.component';
import { AcademyPaymentsComponent } from '../modules/client/academy-payments/academy-payments.component';
import { AcademyPaymentDetailComponent } from '../modules/client/academy-payments/academy-payment-detail/academy-payment-detail.component';

/**
 * Academy Module Routes
 * 
 * Para desactivar el módulo de Academia, comenta todo el contenido de este archivo
 * y elimina la importación en api.routes.ts
 */
export const academyRoutes: Routes = [
	// Academy Students
	{
		path: 'academy/students',
		component: AcademyStudentsComponent,
		data: { title: 'Oportunalia | Estudiantes de Academia' }
	},
	{
		path: 'academy/students/create',
		component: AcademyStudentEditComponent,
		data: { title: 'Oportunalia | Nuevo Estudiante' }
	},
	{
		path: 'academy/students/:id',
		component: AcademyStudentDetailComponent,
		data: { title: 'Oportunalia | Detalle de Estudiante' }
	},
	{
		path: 'academy/students/:id/edit',
		component: AcademyStudentEditComponent,
		data: { title: 'Oportunalia | Editar Estudiante' }
	},
	{
		path: 'academy/students/:id/courses',
		component: AcademyStudentCoursesComponent,
		data: { title: 'Oportunalia | Cursos del Estudiante' }
	},
	{
		path: 'academy/students/:id/views',
		component: AcademyStudentViewsComponent,
		data: { title: 'Oportunalia | Visualizaciones del Estudiante' }
	},

	// Academy Courses
	{
		path: 'academy/courses',
		component: AcademyCoursesComponent,
		data: { title: 'Oportunalia | Cursos de Academia' }
	},
	{
		path: 'academy/courses/create',
		component: AcademyCourseEditComponent,
		data: { title: 'Oportunalia | Nuevo Curso' }
	},
	{
		path: 'academy/courses/:id',
		component: AcademyCourseDetailComponent,
		data: { title: 'Oportunalia | Detalle de Curso' }
	},
	{
		path: 'academy/courses/:id/edit',
		component: AcademyCourseEditComponent,
		data: { title: 'Oportunalia | Editar Curso' }
	},

	// Academy Payments
	{
		path: 'academy/payments',
		component: AcademyPaymentsComponent,
		data: { title: 'Oportunalia | Pagos de Academia' }
	},
	{
		path: 'academy/payments/:id',
		component: AcademyPaymentDetailComponent,
		data: { title: 'Oportunalia | Detalle de Pago' }
	},
];

