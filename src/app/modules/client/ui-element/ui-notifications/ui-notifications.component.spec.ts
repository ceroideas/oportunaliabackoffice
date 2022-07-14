import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UiNotificationsComponent } from './ui-notifications.component';

describe('UiNotificationsComponent', () => {
  let component: UiNotificationsComponent;
  let fixture: ComponentFixture<UiNotificationsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UiNotificationsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UiNotificationsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
