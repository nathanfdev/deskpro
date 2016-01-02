import { createReducer } from 'Ampliflux';
import { async, setFullPayload, togglePayloadInCollection, setValue, handleMassAction }
  from 'Ampliflux/reducers/handlers';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import * as actions from '../Actions/crmListActions';

const initialState = {
  async: {
    done: true
  },
  selected: [],
  // view mode (table or list)
  view: constants.VIEW_MODE_CARD,
  // currently viewed list GET parameters map
  currentListParams: {
    content: 'people',
    sort: 'name',
    order: constants.ORDER_ASC,
    is_deleted: 0
  }
};

export default createReducer(initialState, {
  [actions.load]: async(
    {
      success: (state, payload) =>
        state.set('elements', payload.ids).set('pagination', payload.pagination),
      start: setValue('async.done', false),
      done: setValue('async.done', true)
    }
  ),
  [actions.setParams]: setFullPayload('currentListParams')
});
