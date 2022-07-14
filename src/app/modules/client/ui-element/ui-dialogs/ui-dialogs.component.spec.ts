import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UiDialogsComponent } from './ui-dialogs.component';

describe('UiDialogsComponent', () => {
  let component: UiDialogsComponent;
  let fixture: ComponentFixture<UiDialogsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UiDialogsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UiDialogsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
