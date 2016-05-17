import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { async, setFullPayload, setValue, togglePayloadInCollection } from 'Ampliflux/reducers/handlers';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import * as actions from '../Actions/crmListActions';

export const crmListInitialState = {
  async:             { done: true },
  view:              constants.VIEW_MODE_CARD, // view mode (table or list)
  currentListParams: { // currently viewed list GET parameters map
    content:    'people',
    order_by:   'name',
    order_dir:  constants.ORDER_ASC,
    is_deleted: 0
  },
  visibleFields: {
    people: {
      card:  ['id', 'date_created'],
      table: ['id', 'timezone', 'first_name', 'last_name', 'primary_email', 'date_created', 'date_last_login']
    },
    organizations: {
      card:  ['id', 'date_created'],
      table: ['id', 'date_created', 'importance', 'name', 'summary']
    }
  }
};

export default createReducer(crmListInitialState, {

  [actions.setParams]: setFullPayload('currentListParams'),
  [actions.load]:      async({
    success: (state, payload) =>
      state.set('elements', payload.ids).set('pagination', Immutable.fromJS(payload.pagination)),
    start: setValue('async.done', false),
    done:  setValue('async.done', true)
  }),

  [actions.togglePeopleTableFieldVisibility]: togglePayloadInCollection(['visibleFields', 'people', 'table']),
  [actions.togglePeopleCardFieldVisibility]:  togglePayloadInCollection(['visibleFields', 'people', 'card']),

  [actions.toggleOrgTableFieldVisibility]: togglePayloadInCollection(['visibleFields', 'organizations', 'table']),
  [actions.toggleOrgCardFieldVisibility]:  togglePayloadInCollection(['visibleFields', 'organizations', 'card'])

});
