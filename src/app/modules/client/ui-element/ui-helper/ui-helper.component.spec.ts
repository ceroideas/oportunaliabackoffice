import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UiHelperComponent } from './ui-helper.component';

describe('UiHelperComponent', () => {
  let component: UiHelperComponent;
  let fixture: ComponentFixture<UiHelperComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UiHelperComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UiHelperComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
