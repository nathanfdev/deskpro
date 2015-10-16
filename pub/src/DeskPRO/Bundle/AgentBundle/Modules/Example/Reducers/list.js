import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import * as listActions from '../Actions/listActions';

const initialState = {
  isLoading: false,
  isDone: false
};

export default createReducer(initialState, {
  [listActions.viewFilter]: async({
    start: (state)   => state.set('isLoading', true).set('isDone', false),
    success: (state) => state.set('isLoading', false).set('isDone', true)
  })
});
