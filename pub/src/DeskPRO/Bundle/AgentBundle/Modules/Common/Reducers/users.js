import * as UserActions from "../Actions/UserActions";
import { createReducer } from "Ampliflux";
import { setPayload, setFullPayload, composeHandlers, async, asyncIndicator } from "Ampliflux/reducers/handlers";
import Immutable from "immutable";

const initialState = {
  records:  {},
  requests: {},
  status:   {}
};

function handleSetUserRequest(state, payload) {
  const loadedRecords = payload.records;
  const requestId     = payload.requestId;
  const mode          = payload.mode || 'append';

  let recordIds = Immutable.Set(payload.ids || []);

  if (mode !== 'set') {
    const existRecordIds = state.getIn(['requests', requestId]);
    if (existRecordIds) {
      recordIds = recordIds.merge(existRecordIds);
    }
  }

  return state.merge({
    records:  state.get('records').merge(loadedRecords),
    requests: { [requestId]: recordIds}
  });
};

export default createReducer(initialState, {
  [UserActions.gcUsers]: (state) => {
    return state;
  },

  [UserActions.setUserRequest]: (state, payload) => {
    return handleSetUserRequest(state, payload);
  },

  [UserActions.loadUsers]: composeHandlers(
    asyncIndicator((state, payload) => {
      return {
        loading:  `status.${payload.requestId}.isLoading`,
        success:  `status.${payload.requestId}.isDone`,
      }
    }),
    async({
      success: (state, payload) => {
        return handleSetUserRequest(state, payload);
      }
    })
  )
});
