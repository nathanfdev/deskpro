import { createAction } from 'Ampliflux';
import $ from 'jquery';

export const widgetResize = createAction(
  'WIDGET_RESIZE',
  () => {
    const $window = $(window.widgetFrame);
    return {
      width: $window.width(),
      height: $window.height()
    };
  }
);

export const windowResize = createAction(
  'WINDOW_RESIZE',
  () => dispatch => {
    dispatch(widgetResize());
    const $window = $(parent.window);

    return {
      width: $window.width(),
      height: $window.height()
    };
  }
);

export const loadOptions = createAction('WIDGET_OPTIONS', options => ($.extend(true, {}, options)));

export const openTriggerPopup = createAction('WIDGET_OPEN_TRIGGER_POPUP');
export const closeTriggerPopup = createAction('WIDGET_CLOSE_TRIGGER_POPUP', () => {
  localStorage['dpWidget.dpWindow.popupShown'] = 'none';
});

export const openWidget = createAction('WIDGET_OPEN');
export const closeWidget = createAction('WIDGET_CLOSE');
