import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-ui-progress-bars',
  templateUrl: './ui-progress-bars.component.html',
  styleUrls: ['./ui-progress-bars.component.scss']
})
export class UiProgressBarsComponent implements OnInit {
  type: string;
  stacked: any[] = [];
  constructor() { }

  ngOnInit(): void {
    let types = ['danger', 'warning', 'success'];

    this.stacked = [];
    let n = Math.floor(Math.random() * 4 + 1);
    for (let i = 0; i < n; i++) {
      let index = Math.floor(Math.random() * 4);
      let value = Math.floor(Math.random() * 27 + 3);
      this.stacked.push({
        value,
        type: types[index],
        // label: value + ' %'
      });
    }
  }

}
