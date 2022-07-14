import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-ui-icons',
  templateUrl: './ui-icons.component.html',
  styleUrls: ['./ui-icons.component.scss']
})
export class UiIconsComponent implements OnInit {
  LineTab: boolean = true;
  FontTab: boolean;
  CurrencyTab: boolean;
  WeatherTab: boolean;
  constructor() { }

  ngOnInit(): void {
  }
  onTab(number) {
    this.LineTab = false;
    this.FontTab = false;
    this.CurrencyTab = false;
    this.WeatherTab = false;

    if (number == '1') {
      this.LineTab = true;
    }
    else if (number == '2') {
      this.FontTab = true;
    }
    else if (number == '3') {
      this.CurrencyTab = true;
    }
    else if (number == '4') {
      this.WeatherTab = true;
    }
  }
}
