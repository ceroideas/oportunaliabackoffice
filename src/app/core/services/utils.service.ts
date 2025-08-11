import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { saveAs } from 'file-saver';
import { formatDate } from '@angular/common';

import { environment, endpoint } from 'src/environments/environment';
import { BaseResponse, ErrorResponse } from 'src/app/shared/models/base-response.model';

@Injectable({
	providedIn: 'root'
})
export class UtilsService {

	constructor(
		private toastr: ToastrService,
		public http: HttpClient
	) { }

	/**
	 * Logs an Ajax response, only if it's not in production.
	 */
	logResponse(json: any) {
		if (!environment.production) { console.log(json); }
	}

	/**
	 * Converts a FormGroup to Object.
	 */
	formToObject(form: FormGroup, object: Object) {

		let k = form.controls;

		for (let key of Object.keys(k)) {
			let valor = k[key].value;
			object[key] = valor
		}
		return object;
	}

	parseResponseErrors(form: FormGroup, data: ErrorResponse) {

		let formErrors = {};
		let errors: any = data.error.messages[0];
		console.log(errors);

		Object.keys(errors).forEach(key => {

			let keyErrors = {};

			errors[key].forEach(error => {
				if (/^validation./.test(error)) {
					let errorType = error.split('.')[1];
					keyErrors[errorType] = true;
				}
			});

			if (form.controls[key]) {
				form.controls[key].setErrors(keyErrors);
			}
		});
	}

	/**
	 * Obtains a string with today's date.
	 */
	today() {
		var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0');
		var yyyy = today.getFullYear();

		return `${dd}/${mm}/${yyyy}`;
	}

	/**
	 * Obtains latitude and longitude using the Google Maps API.
	 */
	getLatAndLong(string){
		let url = endpoint(environment.google_maps_geocode, {
			address: string,
			key: environment.google_maps_key
		});
		return this.http.get(url);
	}

	private toastConf = {
		positionClass: 'toast-top-right',
		closeButton: true,
		timeOut: 3000,
	};

	/**
	 * Throws a notification using Toastr.
	 */
	showToast(msg, type?) {

		if (Array.isArray(msg)) {
			msg.forEach(submsg => this.showToast(submsg, type));
			return;
		}

		switch (type) {
			case 'success':
				this.toastr.success(msg, null, this.toastConf);
				break;
			case 'warning':
				this.toastr.warning(msg, null, this.toastConf);
				break
			case 'error':
				this.toastr.error(msg, null, this.toastConf);
				break;
			case 'info':
				this.toastr.info(msg, null, this.toastConf);
				break;
			default:
				this.toastr.success(msg, null, this.toastConf);
				break;
		}
	}

	/**
	 * CKEditor common configuration.
	 * https://ckeditor.com/docs/ckeditor5/latest/features/toolbar/toolbar.html
	 */
	public ckOptions = {
		toolbar: [
			'undo', 'redo', '|',
			'heading', '|',
			'bold', 'italic', /*'underline', */'|',
			/*'fontsize', '|',*/
			/*'cut', 'copy', 'paste', '|',*/
			'numberedList', 'bulletedList', 'link', /*'unlink', */'|',
			'insertTable',
		]
	}

	/**
	 * DataTables common configuration.
	 */
	public dtOptions = {
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, 'Todas'],
		],
		lengthChange: true,
		serverSide: false,
		paging: true,
		fixedColumns:true,
		fixedHeader: true,
		searching: true,
		dom: `<"dataTables_before d-flex flex-row justify-content-between"
				<"dataTables_filters d-flex flex-row"
					<"dataTables_search" f <"icon fa fa-search text-cyan">>
				>
			l>
			<"dataTables_filter_tabs d-flex flex-row">
			tip`,
		responsive: true,
		pagingType: 'full_numbers',
		deferRender: true,
		orderCellsTop: true,
		/*buttons: [
			'copy', 'excel', 'pdf'
		],*/
		language: {
			processing: 'Procesando...',
			search: '',
			searchPlaceholder: 'Buscar...',
			lengthMenu: 'Mostrar _MENU_ elementos',
			info: 'Mostrando desde _START_ al _END_ de _TOTAL_ elementos',
			infoEmpty: 'Mostrando ningún elemento.',
			infoFiltered: '(filtrado _MAX_ elementos total)',
			infoPostFix: '',
			loadingRecords: 'Cargando registros...',
			zeroRecords: 'No se encontraron registros',
			emptyTable: 'No hay datos disponibles en la tabla',
			paginate: {
				first: '<<',
				previous: '<',
				next: '>',
				last: '>>'
			},
			aria: {
				sortAscending: ': Activar para ordenar la tabla en orden ascendente',
				sortDescending: ': Activar para ordenar la tabla en orden descendente'
			}
		}
	};

	normalizeText(text: string): string {
	    return text.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
	}

	/**
	 * Add filters header row to DataTables.
	 */
	addColumnFilters(api, table_id) {

		$(table_id+' thead').addClass('headers');

		$('<thead>', { class: 'filters' })
			.insertAfter(table_id+' thead.headers');

		$('<tr>')
			.appendTo(table_id+' thead.filters');

		// For each column
		api
		.columns()
		.eq(0)
		.each(function (colIdx) {

			$(table_id+' thead.filters tr').append($('<th>'));

			var index = $(api.column(colIdx).header()).index();
			var header = $(table_id+' thead.headers th').eq(index);

			// If the header is not filterable, do not append an input
			if (header.hasClass('not-filterable')) { return; }

			// Set the header cell to contain the input element
			var cell = $('.filters th').eq(index);

			var title = header.text();

			$(cell).html('<input type="text" placeholder="' + title + '" style="width:100%" />');

			// On every keypress in this input
			$('input',
				$('.filters th').eq($(api.column(colIdx).header()).index())
			)
			.off('keyup change')
			.on('keyup change', function (e) {
				e.stopPropagation();

				// Get the search value
				$(this).attr('title', $(this).val() as any);
				var regexr = '({search})'; //$(this).parents('th').find('select').val();

				// Search the column for that value
				api
				.column(colIdx)
				.search(
					(<HTMLInputElement>this).value != '' ?
						regexr.replace('{search}', '(((' + (<HTMLInputElement>this).value + ')))')
						: '',
					(<HTMLInputElement>this).value != '',
					(<HTMLInputElement>this).value == ''
				)
				.draw();
			});
		});
	}

	/**
	 * If responsive is applied to the table, hide filter cells.
	 */
	hideSearchInputs(columns, table_id) {
		for (let i = 0; i < columns.length; i++) {
			if (columns[i]) {
				$(table_id+" thead.filters th:eq(" + i + ")").show();
			} else {
				$(table_id+" thead.filters th:eq(" + i + ")").hide();
			}
		}
	}

	/**
	 * Calculates the difference between two datetimes
	 * and returns it in the form of a readable string.
	 */
	public timeDiffString(before: any, after: any = null): string {

		if (!before) {
			return null;
		} else if (!(before instanceof Date)) {
			before = new Date(before);
		}

		// If there's no 'after' date then calculate against today's date
		if (!after) {
			after = new Date();
		} else if (!(after instanceof Date)) {
			after = new Date(after);
		}

		let diff = before.getTime() - after.getTime();

		if (diff < 0) { return null; }

		let delta = diff / 1000;

		let dd: any = Math.floor(delta / 86400);
		delta -= dd * 86400;

		let hh: any = Math.floor(delta / 3600) % 24;
		delta -= hh * 3600;

		let mm: any = Math.floor(delta / 60) % 60;
		delta -= mm * 60;

		let ss: any = Math.floor(delta % 60);

		hh = hh.toString().padStart(2, '0');
		mm = mm.toString().padStart(2, '0');
		ss = ss.toString().padStart(2, '0');

		return `${dd}d ${hh}:${mm}:${ss}`;
	}

	/**
	 * Extracts the date from a timestamp string ('yyyy-MM-dd HH:mm:ss').
	 */
	public extractDateFrom(timestamp: string): string {
		return timestamp.substring(0,10);
	}

	/**
	 * Extracts the time from a timestamp string ('yyyy-MM-dd HH:mm:ss').
	 */
	public extractTimeFrom(timestamp: string): string {
		return timestamp.substring(11,16);
	}

	/**
	 * Converts a date to a format used in notifications.
	 */
   convertDate(value: string): string {

		let date = new Date(formatDate(value, 'yyyy-MM-dd', 'es'));

    let today = new Date();

		if (date.getFullYear() == today.getFullYear() &&
			date.getMonth() == today.getMonth() &&
			date.getDate() == today.getDate()) {
			return formatDate(value, 'HH:mm', 'es')
		} else {
			return formatDate(value, 'dd/MM/yyyy', 'es')
		}
	}

	/**
	 * Creates a filename.
	 */
	public filename(objectName: string) {

		let today = new Date();
		let dd = String(today.getDate()).padStart(2, '0');
		let mm = String(today.getMonth() + 1).padStart(2, '0');
		let yyyy = today.getFullYear();
		let hh = String(today.getHours()).padStart(2, '0');
		let ii = String(today.getMinutes()).padStart(2, '0');
		let ss = String(today.getSeconds()).padStart(2, '0');

		return `${environment.appName}-${objectName}--${yyyy}${mm}${dd}-${hh}${ii}${ss}`
			.replace(' ','-')
			.toLowerCase();
	}

	/**
	 * Helps download a binary file.
	 */
	public downloadFile(data: any, type: string, objectName: string) {

		let fileType = '', filename = this.filename(objectName);

		switch (type) {
			case 'xlsx': fileType = 'application/ms-excel'; filename += '.xlsx'; break;
			case 'csv': fileType = 'application/csv'; filename += '.csv'; break;
			case 'pdf': fileType = 'application/pdf'; filename += '.pdf'; break;
		}

		let blob = new Blob([data], {type: fileType});
		let url = window.URL.createObjectURL(blob);
		saveAs(data, filename);
	}
}
