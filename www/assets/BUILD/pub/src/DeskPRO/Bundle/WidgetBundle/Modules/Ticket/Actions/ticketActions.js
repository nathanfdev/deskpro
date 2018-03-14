import { createAction } from 'DeskPRO/Component/Ampliflux';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';
import { history } from '../../../Services/history';
import {
  ticketDefaultDepartmentSelector,
  isTicketDepartmentFieldHidden,
  liveDemoSelector,
  ticketDefaultSubjectSelector,
  ticketSelectSubjectTypeSelector,
  ticketDefaultValuesSelector
} from '../../Application/Selectors/dpWindow';

const getNewTicketQueryParams = state => ({
  department_id:         ticketDefaultDepartmentSelector(state),
  hide_department_field: isTicketDepartmentFieldHidden(state),
  subject_type:          ticketSelectSubjectTypeSelector(state),
  subject:               ticketDefaultSubjectSelector(state)
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

    const queryParams = {
      type: 'widget',
      ...getNewTicketQueryParams(state)
    };

    let defaultValues = ticketDefaultValuesSelector(state);
    if (defaultValues) {
      defaultValues = defaultValues.toJS();

      // convert message format
      if (defaultValues.message) {
        defaultValues.message = { message: defaultValues.message };
      }

      // convert custom fields
      const convertCustomFields = (groupKey, fieldPrefix) => {
        if (defaultValues[groupKey] && typeof defaultValues[groupKey] === 'object') {
          Object.keys(defaultValues[groupKey]).forEach((key) => {
            defaultValues[`${fieldPrefix}_${key}`] = {};
            defaultValues[`${fieldPrefix}_${key}`].data = defaultValues[groupKey][key];
          });

          delete defaultValues[groupKey];
        }
      };

      convertCustomFields('fields', 'ticket_field');
      convertCustomFields('user_fields', 'user_field');
      convertCustomFields('organization_fields', 'org_field');

      queryParams.ticket = defaultValues;
    }

    const promise = widgetApi.sendGet(`DP_API/tickets/new?${compileParams(queryParams)}`, { ...ajaxOptions(state) });
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
    const promise = widgetApi.sendPost(`DP_API/tickets/new?${compileParams(queryParams)}`, params, { ...ajaxOptions(state) });
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
