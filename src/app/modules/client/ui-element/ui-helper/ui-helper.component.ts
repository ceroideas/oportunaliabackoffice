import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-ui-helper',
  templateUrl: './ui-helper.component.html',
  styleUrls: ['./ui-helper.component.scss']
})
export class UiHelperComponent implements OnInit {
  isFull: boolean;
  isFull1: boolean;
  isFull2: boolean;
  isFull3: boolean;
  isFull4: boolean;
  isFull5: boolean;
  constructor() { }

  ngOnInit(): void {
  }

  fullScreenSection(number) {
    if (number == 1) {
      if (this.isFull) {
        this.isFull = false;
      }
      else {
        this.isFull = true;
      }
    }
    else if (number == 2) {
      if (this.isFull1) {
        this.isFull1 = false;
      }
      else {
        this.isFull1 = true;
      }
    }
    else if (number == 3) {
      if (this.isFull2) {
        this.isFull2 = false;
      }
      else {
        this.isFull2 = true;
      }
    }
    else if (number == 4) {
      if (this.isFull3) {
        this.isFull3 = false;
      }
      else {
        this.isFull3 = true;
      }
    }
    else if (number == 5) {
      if (this.isFull4) {
        this.isFull4 = false;
      }
      else {
        this.isFull4 = true;
      }
    }
    else if (number == 6) {
      if (this.isFull5) {
        this.isFull5 = false;
      }
      else {
        this.isFull5 = true;
      }
    }

  }
}
