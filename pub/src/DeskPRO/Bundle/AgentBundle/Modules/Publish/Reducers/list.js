import { createReducer } from 'Ampliflux';
import { async, setFullPayload, togglePayloadInCollection, setValue, handleMassAction }
  from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/publishListActions';
import * as massActions from '../Actions/publishMassActions.js';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  async: {
    done: true
  },
  selected: [],
  // view mode (table or list)
  view: constants.VIEW_MODE_CARD,
  // currently viewed list GET parameters map
  currentListParams: {
    sort: 'date_created',
    order: constants.ORDER_DESC,
    // which list is displayed
    content: 'articles'
  }
};

export default createReducer(initialState, {
  [actions.load]:
    async({
      success: (state, payload) =>
        state.set('elements', payload.ids).set('pagination', payload.pagination),
      start: setValue('async.done', false),
      done: setValue('async.done', true)
    }
  ),
  [actions.setParams]: setFullPayload('currentListParams'),
  [massActions.toggleMassAction]: handleMassAction('elements', 'selected'),
  [massActions.toggleSelectedAction]: togglePayloadInCollection('selected')
});
