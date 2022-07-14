import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UiProgressBarsComponent } from './ui-progress-bars.component';

describe('UiProgressBarsComponent', () => {
  let component: UiProgressBarsComponent;
  let fixture: ComponentFixture<UiProgressBarsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UiProgressBarsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UiProgressBarsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
