import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-ui-notifications',
  templateUrl: './ui-notifications.component.html',
  styleUrls: ['./ui-notifications.component.scss']
})
export class UiNotificationsComponent implements OnInit {
  toastRef: any;

  constructor(private toastr: ToastrService) { }

  ngOnInit(): void {
  }

  showInfoToasterPosotion() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-bottom-right',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showInfoToasterPosotion1() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-bottom-left',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showInfoToasterPosotion2() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-top-left',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showInfoToasterPosotion3() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-top-right',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showInfoToasterPosotion4() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-top-full-width',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showInfoToasterPosotion5() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-bottom-full-width',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showInfoToasterPosotion6() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-top-center',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showInfoToasterPosotion7() {
    this.toastRef = this.toastr.info("Hi, I'm here", null, {
      positionClass: 'toast-bottom-center',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showMessageContext() {
    this.toastRef = this.toastr.info("This is general theme info", null, {
      positionClass: 'toast-bottom-right',
      closeButton: true,
      timeOut: 1000,
    });
  }
  showMessageContext1() {
    this.toastRef = this.toastr.success("This is success info", null, {
      positionClass: 'toast-bottom-right',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showMessageContext2() {
    this.toastRef = this.toastr.warning("This is warning info", null, {
      positionClass: 'toast-bottom-right',
      closeButton: true,
      timeOut: 1000,
    });

  }
  showMessageContext3() {
    this.toastRef = this.toastr.error("This is error info", null, {
      positionClass: 'toast-bottom-right',
      closeButton: true,
      timeOut: 1000,
    });

  }
}
