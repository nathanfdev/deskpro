import { createAction } from 'Ampliflux';

export const windowResize = createAction('WIDGET_WINDOW_RESIZE', (width, height) => ({ width, height }));
export const loadOptions = createAction('WIDGET_OPTIONS');
export const openWidget = createAction('WIDGET_OPEN');
export const closeWidget = createAction('WIDGET_CLOSE');
