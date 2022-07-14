import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-search-results',
  templateUrl: './search-results.component.html',
  styleUrls: ['./search-results.component.scss']
})
export class SearchResultsComponent implements OnInit {
  ClassicTab: boolean = true;
  ArticlesTab: boolean;
  PhotosTab: boolean;
  UsersTab: boolean;
  constructor() { }

  ngOnInit(): void {
  }

  onTab(number) {
    this.ClassicTab = false;
    this.ArticlesTab = false;
    this.PhotosTab = false;
    this.UsersTab = false;
    if (number == '1') {
      this.ClassicTab = true;
    }
    else if (number == '2') {
      this.ArticlesTab = true;
    }
    else if (number == '3') {
      this.PhotosTab = true;
    }
    else if (number == '4') {
      this.UsersTab = true;
    }
  }
}
