import * as TestActions from "../Actions/TestActions";
import { createReducer } from "Ampliflux";
import { setPayload, setFullPayload, composeHandlers, async, asyncIndicator } from "Ampliflux/reducers/handlers";
import Immutable from "immutable";

const initialState = {
  count: 0,
  user: {},
  status: {}
};

export default createReducer(initialState, {
  [TestActions.setCount]: setPayload('count', null),

  [TestActions.loadUser]: composeHandlers(
    asyncIndicator('status.userIsLoading'),
    async({
      success: (state, payload) => {
        return state.set('user', Immutable.fromJS(payload.getData().data.person));
      }
    })
  )
});
