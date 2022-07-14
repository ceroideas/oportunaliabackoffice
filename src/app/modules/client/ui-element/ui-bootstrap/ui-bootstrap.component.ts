import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-ui-bootstrap',
  templateUrl: './ui-bootstrap.component.html',
  styleUrls: ['./ui-bootstrap.component.scss']
})
export class UiBootstrapComponent implements OnInit {
  isCollapsed = true;
  ToggleFirstCollapsed = true;
  ToggleSecondCollapsed = true;
  ToggleBothCollapsed = true;
  accordionitemcollapse1 = true;
  accordionitemcollapse2 = true;
  accordionitemcollapse3 = true;
  accordionitemcollapse4 = true;
  accordionitemcollapse5 = true;
  accordionitemcollapse6 = true;
  constructor() { }

  //  Alets Dismissal
  dismissible = true;
  defaultAlerts: any[] = [
    {
      type: 'info',
      msg: `The system is running well`
    },
    {
      type: 'success',
      msg: `Your settings have been succesfully saved.`
    },
    {
      type: 'warning',
      msg: `Warning, check your permission settings.`
    },
    {
      type: 'danger',
      msg: `Your account has been suspended.`
    }
  ];
  alerts = this.defaultAlerts;

  reset(): void {
    this.alerts = this.defaultAlerts;
  }

  onClosed(dismissedAlert: any): void {
    this.alerts = this.alerts.filter(alert => alert !== dismissedAlert);
  }
  ngOnInit(): void {
  }

}
