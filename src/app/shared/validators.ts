import { FormGroup, AbstractControl, ValidatorFn, ValidationErrors } from '@angular/forms';

export const formErrors = (form: FormGroup, submitted: boolean, key: string, type: string = null) => {
	let control = form.controls[key];
	if (!control || !control.errors || !submitted) { return false; }
	return type ? control.errors[type] : control.errors;
}

export const lte = (max: number) => {
	return (c: AbstractControl): {[key: string]: any} => {
		if (c.value) {
			if (c.value > max) { return { lt: true }; }
		}
	}
}

export const minLengthArray = (min: number) => {
	return (c: AbstractControl): {[key: string]: any} => {
		if (c.value.length >= min) { return null; }
		return { min_length_array: true };
	}
}

export const dateCoherence = (): ValidatorFn => {
	return (form: FormGroup): ValidationErrors => {

		let startDate = form.controls.start_date.value;
		let startTime = form.controls.start_time.value;
		let endDate = form.controls.end_date.value;
		let endTime = form.controls.end_time.value;
		if (!startDate || !startTime || !endDate || !endTime) { return; }

		let start = `${startDate} ${startTime}:00`;
		let end = `${endDate} ${endTime}:00`;

		if (start >= end) {
			form.controls.end_date.setErrors({ coherence: true });
		} else {
			form.controls.end_date.setErrors(null);
		}
	}
}
