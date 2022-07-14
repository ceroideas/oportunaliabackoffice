import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UiNestableComponent } from './ui-nestable.component';

describe('UiNestableComponent', () => {
  let component: UiNestableComponent;
  let fixture: ComponentFixture<UiNestableComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UiNestableComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UiNestableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
