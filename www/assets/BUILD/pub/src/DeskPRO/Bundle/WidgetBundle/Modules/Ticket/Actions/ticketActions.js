import { createAction } from 'DeskPRO/Component/Ampliflux';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';
import { history } from '../../../Services/history';
import { ticketDefaultDepartmentSelector, isTicketDepartmentFieldHidden, liveDemoSelector } from '../../Application/Selectors/dpWindow';

const getNewTicketQueryParams = state => ({
  department_id:         ticketDefaultDepartmentSelector(state),
  hide_department_field: isTicketDepartmentFieldHidden(state)
});

export const setNewTicketFormContent = createAction('WIDGET_SET_NEW_TICKET_FORM_CONTENT');

// Api actions
export const loadTicketDisplayFields = createAction(
  'WIDGET_LOAD_TICKET_DISPLAY_FIELDS',
  () => widgetApi.sendGet('DP_API/tickets/display.js', { ...ajaxOptions, dataType: 'script' })
);

export const loadNewTicketForm = createAction(
  'WIDGET_LOAD_NEW_TICKET_FORM',
  () => (dispatch, getState) => {
    const state = getState();
    const queryParams = getNewTicketQueryParams(state);

    const promise = widgetApi.sendGet(`DP_API/tickets/new?${compileParams(queryParams)}`, { ...ajaxOptions });
    promise.success(response => dispatch(setNewTicketFormContent(response.data)));

    return promise;
  }
);

export const saveNewTicketForm = createAction(
  'WIDGET_SAVE_NEW_TICKET_FORM',
  params => (dispatch, getState) => {
    const state = getState();
    const liveDemo = liveDemoSelector(state);
    if (liveDemo) {
      return null;
    }

    const queryParams = getNewTicketQueryParams(state);
    const promise = widgetApi.sendPost(`DP_API/tickets/new?${compileParams(queryParams)}`, params, { ...ajaxOptions });
    promise.then(
      (response) => {
        if (response.data && response.data.ticket_id) {
          history.replace('ticket/form_submitted');
        } else {
          dispatch(setNewTicketFormContent(response.data));
        }
      },
      response => dispatch(setNewTicketFormContent(response.data.data))
    );

    return promise;
  }
);

export const bootstrapTicketApp = createAction(
  'WIDGET_LOAD_TICKET_APP',
  () => dispatch => new Promise((resolve) => {
    Promise.all([
      dispatch(loadNewTicketForm()),
      dispatch(loadTicketDisplayFields())
    ]).then((response) => {
      resolve(response);
    });
  })
);
