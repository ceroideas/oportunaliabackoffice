import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UiListGroupComponent } from './ui-list-group.component';

describe('UiListGroupComponent', () => {
  let component: UiListGroupComponent;
  let fixture: ComponentFixture<UiListGroupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UiListGroupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UiListGroupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
