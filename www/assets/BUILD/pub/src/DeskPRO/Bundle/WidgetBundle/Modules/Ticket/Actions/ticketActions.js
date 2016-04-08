import { createAction } from 'Ampliflux';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';

export const setNewTicketFormContent = createAction('WIDGET_SET_NEW_TICKET_FORM_CONTENT');

// Api actions
export const loadTicketDisplayFields = createAction(
  'WIDGET_LOAD_TICKET_DISPLAY_FIELDS',
  () => widgetApi.sendGet('DP_API/tickets/display.js', { ...ajaxOptions, dataType: 'script' })
);

export const loadNewTicketForm = createAction(
  'WIDGET_LOAD_NEW_TICKET_FORM',
  () => dispatch => {
    const promise = widgetApi.sendGet('DP_API/tickets/new', { ...ajaxOptions });
    promise.success(response => dispatch(setNewTicketFormContent(response.data)));

    return promise;
  }
);

export const saveNewTicketForm = createAction(
  'WIDGET_SAVE_NEW_TICKET_FORM',
  params => dispatch => {
    const promise = widgetApi.sendPost('DP_API/tickets/new', params, { ...ajaxOptions });
    promise.then(
      response => dispatch(setNewTicketFormContent(response.data)),
      response => dispatch(setNewTicketFormContent(response.data.data))
    );

    return promise;
  }
);
