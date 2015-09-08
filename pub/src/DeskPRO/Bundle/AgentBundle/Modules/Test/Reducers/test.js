import * as TestActions from "../Actions/TestActions";
import { createReducer } from "Ampliflux";
import { setPayload, setFullPayload, composeHandlers, async, asyncIndicator } from "Ampliflux/reducers/handlers";
import { Map } from "immutable";

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
        return state.set('user', Map(payload.getData().data.person));
      }
    })
  )
});
