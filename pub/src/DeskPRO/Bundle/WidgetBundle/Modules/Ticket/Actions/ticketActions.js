import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';

export const loadForm = createAction(
  'WIDGET_TICKET_LOAD_FORM',
  () => {
    return new Promise(resolve => {
      DpApi
        .sendGet(`DP_API/tickets/new`, {...ajaxOptions})
        .success(response => resolve(response.data));
    });
  }
);

export const saveForm = createAction(
  'WIDGET_TICKET_SAVE_FORM',
  params => {
    return new Promise(resolve => {
      DpApi
        .sendPost(`DP_API/tickets/new`, params, {...ajaxOptions})
        .success(response => resolve(response.data));
    });
  }
);
