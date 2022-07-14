import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-ui-range-sliders',
  templateUrl: './ui-range-sliders.component.html',
  styleUrls: ['./ui-range-sliders.component.scss']
})
export class UiRangeSlidersComponent implements OnInit {

  constructor() { }

  ngOnInit(): void {
  }
  formatLabel(value: number) {
    if (value >= 1000) {
      return Math.round(value / 1000) + 'k';
    }

    return value;
  }
}
