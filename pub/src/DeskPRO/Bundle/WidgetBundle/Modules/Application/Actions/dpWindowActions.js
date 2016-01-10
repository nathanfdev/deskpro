import { createAction } from 'Ampliflux';
import $ from 'jquery';
import { widgetOpenedSelector, widgetTypeSelector } from '../Selectors/dpWindow';

export const openWidget = createAction('WIDGET_OPEN');
export const closeWidget = createAction('WIDGET_CLOSE');

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
export const reloadOptions = createAction(
  'WIDGET_RELOAD_OPTIONS',
  options => (dispatch, getState) => {
    const state = getState();

    const widgetOpened = widgetOpenedSelector(state);
    const widgetType = widgetTypeSelector(state);

    dispatch(loadOptions(options));
    dispatch(windowResize());

    if (widgetOpened) {
      if (widgetType !== options.widget.type) {
        dispatch(closeWidget());
        setTimeout(() => dispatch(openWidget()), 350);
      }
    }
  }
);

export const openTriggerPopup = createAction('WIDGET_OPEN_TRIGGER_POPUP');
export const closeTriggerPopup = createAction('WIDGET_CLOSE_TRIGGER_POPUP', () => {
  localStorage['dpWidget.dpWindow.popupShown'] = 'none';
});

