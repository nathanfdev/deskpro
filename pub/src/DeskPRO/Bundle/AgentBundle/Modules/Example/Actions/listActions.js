import { createAction } from 'Ampliflux';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import * as widgetActions from '../RecordStores/Actions/widgetActions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

export const viewFilter = createAction('EG_WIDGET_LOAD_FILTER', (params) => dispatch => {
  dispatch(widgetActions.releaseRequest('view_filter'));

  // Doing an AJAX request to set the widgets used by the list
  // using a request id 'view_filter'
  const p1 = api.sendGet('DP_API/sandbox_widgets?type=' + params.widgetType).then(result => {
    dispatch(widgetActions.setWidgets('view_filter', mapKeyedFromArray(result.getData().data, 'id')));
  });

  return Promise.all([p1]);
});
