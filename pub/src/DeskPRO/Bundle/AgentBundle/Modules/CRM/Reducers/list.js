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
    sort: 'date_created',
    order: constants.ORDER_DESC,
    is_deleted: 0
  }
};

export default createReducer(initialState, {
  [actions.load]:
    async({
      success: (state, payload) =>
        state.set(payload.content, payload.data.data).set('pagination', payload.data.meta.pagination),
      start: setValue('async.done', false),
      done: setValue('async.done', true)
    }
  ),
  [actions.setParams]: setFullPayload('currentListParams')
});
