import { createReducer } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { async, setFullPayload, setValue } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import { constants } from '../../../Constants/Constants';
import * as actions from '../Actions/crmListActions';

export const crmListInitialState = {
  async:             { done: true },
  view:              constants.VIEW_MODE_CARD, // view mode (table or list)
  elements:          [],
  selected:          [],
  currentListParams: { // currently viewed list GET parameters map
    content:    'people',
    order_by:   'name',
    order_dir:  constants.ORDER_ASC,
    is_deleted: 0
  },

  fields: {
    people: {
      [constants.VIEW_MODE_CARD]: [
        { id: 'date_created', title: 'Date Created', visible: true },
        { id: 'language', title: 'Language', visible: true }
      ],

      [constants.VIEW_MODE_TABLE]: [
        { id: 'id', title: 'ID', visible: true },
        { id: 'timezone', title: 'Timezone', visible: true },
        { id: 'first_name', title: 'First Name', visible: true },
        { id: 'last_name', title: 'Last Name', visible: true },
        { id: 'primary_email', title: 'Email', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true },
        { id: 'date_last_login', title: 'Last Login', visible: true }
      ]
    },

    org: {
      [constants.VIEW_MODE_CARD]: [
        { id: 'date_created', title: 'Date Created', visible: true }
      ],

      [constants.VIEW_MODE_TABLE]: [
        { id: 'id', title: 'ID', visible: true },
        { id: 'date_created', title: 'Date Created', visible: true },
        { id: 'name', title: 'Name', visible: true },
        { id: 'summary', title: 'Summary', visible: true },
        { id: 'tickets_count', title: 'Tickets', visible: true }
      ]
    }
  }
};

export default createReducer(crmListInitialState, {

  [actions.setParams]: setFullPayload('currentListParams'),

  [actions.load]: async(
    {
      success: (state, payload) =>
                 state.set('elements', Immutable.List(payload.ids)).set('pagination', Immutable.fromJS(payload.pagination)),

      start: setValue('async.done', false),
      done:  setValue('async.done', true)
    }),

  [actions.togglePeopleFieldVisibility]: (state, { type, index }) => {
    const old = state.getIn(['fields', 'people', type, index, 'visible']);
    if (undefined === old) return state;
    return state.setIn(['fields', 'people', type, index, 'visible'], !old);
  },

  [actions.changePeopleFieldOrder]: (state, { type, from, to }) => {
    const fromField = state.getIn(['fields', 'people', type, from]);
    const toField   = state.getIn(['fields', 'people', type, to]);
    if (undefined === fromField || undefined === toField) return state;
    return state
      .setIn(['fields', 'people', type, from], toField)
      .setIn(['fields', 'people', type, to], fromField);
  },

  [actions.toggleOrgFieldVisibility]: (state, { type, index }) => {
    const old = state.getIn(['fields', 'org', type, index, 'visible']);
    if (undefined === old) return state;
    return state.setIn(['fields', 'org', type, index, 'visible'], !old);
  },

  [actions.changeOrgFieldOrder]: (state, { type, from, to }) => {
    const fromField = state.getIn(['fields', 'org', type, from]);
    const toField   = state.getIn(['fields', 'org', type, to]);
    if (undefined === fromField || undefined === toField) return state;
    return state
      .setIn(['fields', 'org', type, from], toField)
      .setIn(['fields', 'org', type, to], fromField);
  }

});
