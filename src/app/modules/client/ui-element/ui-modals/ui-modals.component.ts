import { Component, OnInit, TemplateRef } from '@angular/core';
import { BsModalService, BsModalRef } from 'ngx-bootstrap/modal';

@Component({
  selector: 'app-ui-modals',
  templateUrl: './ui-modals.component.html',
  styleUrls: ['./ui-modals.component.scss']
})
export class UiModalsComponent implements OnInit {
  modalRef: BsModalRef;
  constructor(private modalService: BsModalService) { }

  ngOnInit(): void {
  }
  openModalWithClass(template: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template,
      Object.assign({}, { class: 'gray modal-lg' })
    );
  }
  openSmallModalWithClass(template: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template,
      Object.assign({}, { class: 'gray modal-sm' })
    );
  }

  openModalWithClass2(template2: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template2,
      Object.assign({}, { class: 'gray modal-md' })
    );
  }
  openModalWithClass3(template3: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template3,
      Object.assign({}, { class: 'gray modal-md modal-dialog-centered' })
    );
  }
  LaunchModal1WithClass(template4: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template4,
      Object.assign({}, { class: 'gray modal-lg' })
    );
  }
  LaunchModal2WithClass(template5: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template5,
      Object.assign({}, { class: 'gray modal-sm' })
    );
  }
  LaunchModal3WithClass(template6: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template6,
      Object.assign({}, { class: 'gray modal-md' })
    );
  }
  LaunchModal4WithClass(template7: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template7,
      Object.assign({}, { class: 'gray modal-md modal-dialog-centered' })
    );
  }
  LaunchModal5WithClass(template8: TemplateRef<any>) {
    this.modalRef = this.modalService.show(
      template8,
      Object.assign({}, { class: 'gray modal-md' })
    );
  }
}