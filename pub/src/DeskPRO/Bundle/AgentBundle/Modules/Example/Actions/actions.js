import { createAction } from 'Ampliflux';

export const setWidgetFilter = createAction('EG_SET_WIDGET_FILTER', (params) => {
  return { params };
});
