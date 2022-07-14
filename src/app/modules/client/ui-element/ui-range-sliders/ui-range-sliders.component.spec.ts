import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UiRangeSlidersComponent } from './ui-range-sliders.component';

describe('UiRangeSlidersComponent', () => {
  let component: UiRangeSlidersComponent;
  let fixture: ComponentFixture<UiRangeSlidersComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UiRangeSlidersComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UiRangeSlidersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
