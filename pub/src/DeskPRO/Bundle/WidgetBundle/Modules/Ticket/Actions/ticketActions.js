import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';

export const loadNewTicketForm = createAction(
  'WIDGET_LOAD_NEW_TICKET_FORM',
  () => {
    return new Promise(resolve => {
      DpApi
        .sendGet(`DP_API/tickets/new`, {...ajaxOptions})
        .success(response => resolve(response.data));
    });
  }
);

export const saveNewTicketForm = createAction(
  'WIDGET_SAVE_NEW_TICKET_FORM',
  params => {
    return new Promise(resolve => {
      DpApi
        .sendPost(`DP_API/tickets/new`, params, {...ajaxOptions})
        .success(response => resolve(response.data));
    });
  }
);
