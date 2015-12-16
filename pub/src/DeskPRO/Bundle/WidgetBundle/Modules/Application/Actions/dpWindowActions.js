import { createAction } from 'Ampliflux';

export const windowResize = createAction('WINDOW_RESIZE', (width, height) => ({ width, height }));
export const openWidget = createAction('WIDGET_OPEN');
export const closeWidget = createAction('WIDGET_CLOSE');
