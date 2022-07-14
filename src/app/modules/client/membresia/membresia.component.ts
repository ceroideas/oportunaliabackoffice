import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { formatDate } from '@angular/common';
import { Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { Subject } from 'rxjs';
import { DataTableDirective } from 'angular-datatables';

import { endpoint } from 'src/environments/environment';
import { UtilsService } from 'src/app/core/services/utils.service';
import { CommunicationsService } from 'src/app/core/services/communications.service';

@Component({
  selector: 'app-membresia',
  templateUrl: './membresia.component.html',
  styleUrls: ['./membresia.component.scss']
})
export class MembresiaComponent implements OnInit {

	// DataTables

	@ViewChild(DataTableDirective) dtElement: DataTableDirective;
	public dtOptions: DataTables.Settings = {};
	public dtTrigger: Subject<any> = new Subject<any>();
	private filter_tab: string = 'all';

	// Modals

	@ViewChild('confirmModal') confirmModal: TemplateRef<any>;
	public modalRef: BsModalRef;
	private deleteId: number;

  constructor(
    private router: Router,
		private modalService: BsModalService,
		public utils: UtilsService,
		public communicationsService: CommunicationsService
  ) { }

  ngOnInit(): void {
		this.initTable();
	}

	ngOnDestroy(): void {
		this.dtTrigger.unsubscribe();
	}

	ngAfterViewInit(): void {
		this.dtTrigger.next();
	}

	initTable(): void {

		let that = this;

		this.dtOptions = {
			...this.utils.dtOptions,
			ajax: {
				url: endpoint('membresia_dt'),
				type: 'get',
				headers: {
					Authorization: sessionStorage.getItem('token')
				},
				dataSrc: function (json) {
					that.utils.logResponse(json);
					return json['response'];
				}
			},
			columns: [
        {
					title: 'Referencia', data: 'id',
				},
        {
					title: 'Nota', data: 'note',
				},
        {
					title: 'Referencia subasta', data: 'auction_id',
				},
        {
					title: 'Referencia usuario', data: 'user_id',
				},
        {
					title: 'Nombre subasta', data: 'title',
				},
        {
					title: 'Nombre de usuario', data: 'username',
				},
				{
					title: 'Acciones', orderable: false, data: null, className: 'all not-filterable',
					render: function () {

						let render = '';

						render += `<button class="borrar btn btn-table btn-red" title="Borrar">
							<i class="fa fa-trash"></i>
						</button>`;

						return render;
					}
				}
			],
			columnDefs:[
				{ targets: -1, orderable: false },
			],
			rowCallback: (row: Node, data: any[] | Object, index: number) => {

				$('button.borrar', row).unbind('click');
				$('button.borrar', row).bind('click', () => {
					this.confirmDelete(data['id']);
				});

				return row;
			},
			initComplete: function(settings, json) {

			}
		};
	}

	reloadTable() {
		let newUrl = endpoint('membresia_dt');

		this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
			dtInstance.ajax.url(newUrl).load(null, false);
		});
	}

	confirmDelete(id: number) {
		this.deleteId = id;
		this.modalRef = this.modalService.show(this.confirmModal, { class: 'modal-sm' });
	}

	deleteMembresia() {

		this.communicationsService.deleteMembresia(this.deleteId)
		.subscribe(data => {
			this.reloadTable();
			this.utils.showToast('La membresía ha sido borrada');
			this.modalRef.hide();
		});
	}

}
