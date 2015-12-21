import { createAction } from 'Ampliflux';
import $ from 'jquery';

export const windowResize = createAction(
  'WIDGET_WINDOW_RESIZE',
  () => ({
    width: $(window.widgetFrame).width(),
    height: $(window.widgetFrame).height()
  })
);

export const loadOptions = createAction('WIDGET_OPTIONS', options => ({...options}));
export const openWidget = createAction('WIDGET_OPEN');
export const closeWidget = createAction('WIDGET_CLOSE');
