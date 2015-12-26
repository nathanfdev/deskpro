import { createAction } from 'Ampliflux';
import $ from 'jquery';

export const windowResize = createAction(
  'WINDOW_RESIZE',
  () => ({
    width: $(parent.window).width(),
    height: $(parent.window).height()
  })
);

export const widgetResize = createAction(
  'WIDGET_RESIZE',
  () => ({
    width: $(window.widgetFrame).width(),
    height: $(window.widgetFrame).height()
  })
);

export const loadOptions = createAction('WIDGET_OPTIONS', options => ({...options}));

export const openTriggerPopup = createAction('WIDGET_OPEN_TRIGGER_POPUP');
export const closeTriggerPopup = createAction('WIDGET_CLOSE_TRIGGER_POPUP', () => {
  localStorage['dpWidget.dpWindow.popupShown'] = 'none';
});

export const openWidget = createAction('WIDGET_OPEN');
export const closeWidget = createAction('WIDGET_CLOSE');
