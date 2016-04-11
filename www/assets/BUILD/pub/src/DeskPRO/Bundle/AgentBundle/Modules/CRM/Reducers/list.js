import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { async, setFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { load, setParams } from '../Actions/crmListActions';

export const crmListInitialState = {
  async:             { done: true },
  view:              constants.VIEW_MODE_CARD, // view mode (table or list)
  currentListParams: { // currently viewed list GET parameters map
    content:    'people',
    order_by:   'name',
    order_dir:  constants.ORDER_ASC,
    is_deleted: 0
  }
};

export default createReducer(crmListInitialState, {

  [setParams]: setFullPayload('currentListParams'),

  [load]: async({
    success: (state, payload) =>
               state.set('elements', payload.ids).set('pagination', Immutable.fromJS(payload.pagination)),

    start: setValue('async.done', false),
    done:  setValue('async.done', true)
  })

});
