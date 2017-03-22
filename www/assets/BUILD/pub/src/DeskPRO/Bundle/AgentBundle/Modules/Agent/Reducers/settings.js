import Immutable from 'immutable';
import { createReducer } from 'Ampliflux';
import { setFullPayload } from 'Ampliflux/reducers/handlers';
import { setAgentSettings, updateFilterGrouping } from '../Actions/settingsActions';
import { hideChat, updateChatsOrder } from '../../IM/Actions/chatsActions';

const initialState = {};
export default createReducer(initialState, {
  [setAgentSettings]:     setFullPayload(),
  [updateFilterGrouping]: (state, { filterId, prefId, groupBy }) => state
    .setIn(['tickets', 'filter_groupings', String(filterId)],
           Immutable.fromJS({ id: prefId, main_grouping: groupBy })),
  [hideChat]: (state, payload) => {
    let newState = state;
    Object.keys(payload).forEach((id) => {
      if (newState.hasIn(['im', 'chats_order', id])) {
        newState = newState.deleteIn(['im', 'chats_order', id]);
      }
    });

    return newState;
  },
  [updateChatsOrder]: (state, payload) => state.setIn(['im', 'chats_order'], payload)
});
