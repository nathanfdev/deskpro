import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/ticketActions';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';

const initialState = {
  content: null
};

export default createReducer(initialState, {
  [actions.loadForm]: async({
    success: setFullPayload('content')
  })
});
