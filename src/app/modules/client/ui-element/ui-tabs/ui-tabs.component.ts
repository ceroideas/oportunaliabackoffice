import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-ui-tabs',
  templateUrl: './ui-tabs.component.html',
  styleUrls: ['./ui-tabs.component.scss']
})
export class UiTabsComponent implements OnInit {
  homeTab: boolean = true;
  profileTab: boolean;
  contactTab: boolean;
  iconhomeTab: boolean = true;
  iconprofileTab: boolean;
  iconcontactTab: boolean;
  onlyiconhomeTab: boolean = true;
  onlyiconprofileTab: boolean;
  onlyiconcontactTab: boolean;
  activeTab: boolean = true;
  isDropup = true;
  homeEX2Tab: boolean = true;
  profileEX2Tab: boolean;
  ContactEX2Tab: boolean;
  homeEX3Tab: boolean = true;
  profileEX3Tab: boolean;
  ContactEX3Tab: boolean;
  homeEX4Tab: boolean = true;
  profileEX4Tab: boolean;
  ContactEX4Tab: boolean;
  icononlycontactTab: any=false;
  constructor() { }

  ngOnInit(): void {
  }
  onTab(number) {
    this.homeTab = false;
    this.profileTab = false;
    this.contactTab = false;

    if (number == '1') {
      this.homeTab = true;
    }
    else if (number == '2') {
      this.profileTab = true;
    }
    else if (number == '3') {
      this.contactTab = true;
    }
  }
  oniconTab(number) {
    this.iconhomeTab = false;
    this.iconprofileTab = false;
    this.iconcontactTab = false;

    if (number == '1') {
      this.iconhomeTab = true;
    }
    else if (number == '2') {
      this.iconprofileTab = true;
    }
    else if (number == '3') {
      this.iconcontactTab = true;
    }
  }
  ononlyiconTab(number) {
    this.onlyiconhomeTab = false;
    this.onlyiconprofileTab = false;
    this.onlyiconcontactTab = false;

    if (number == '1') {
      this.onlyiconhomeTab = true;
    }
    else if (number == '2') {
      this.onlyiconprofileTab = true;
    }
    else if (number == '3') {
      this.onlyiconcontactTab = true;
    }
  }

  onDropDownTab(number) {
    this.activeTab = false;
    if (number == '1') {
      this.activeTab = true;
    }
  }

  onTabEX2(number) {
    this.homeEX2Tab = false;
    this.profileEX2Tab = false;
    this.ContactEX2Tab = false;

    if (number == '1') {
      this.homeEX2Tab = true;
    }
    else if (number == '2') {
      this.profileEX2Tab = true;
    }
    else if (number == '3') {
      this.ContactEX2Tab = true;
    }
  }
  onTabEX3(number) {
    this.homeEX3Tab = false;
    this.profileEX3Tab = false;
    this.ContactEX3Tab = false;

    if (number == '1') {
      this.homeEX3Tab = true;
    }
    else if (number == '2') {
      this.profileEX3Tab = true;
    }
    else if (number == '3') {
      this.ContactEX3Tab = true;
    }
  }
  onTabEX4(number) {
    this.homeEX4Tab = false;
    this.profileEX4Tab = false;
    this.ContactEX4Tab = false;

    if (number == '1') {
      this.homeEX4Tab = true;
    }
    else if (number == '2') {
      this.profileEX4Tab = true;
    }
    else if (number == '3') {
      this.ContactEX4Tab = true;
    }
  }

}
