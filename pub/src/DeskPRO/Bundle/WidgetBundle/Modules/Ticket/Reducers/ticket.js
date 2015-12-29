import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/ticketActions';
import { async, setFullPayload, setValue, setValueOnError } from 'Ampliflux/reducers/handlers';

const initialState = {
  content: null,
  loading: false
};

export default createReducer(initialState, {
  [actions.loadForm]: async({
    success: setFullPayload('content'),
    start: setValue('loading', true),
    done: setValue('loading', false),
    error: setValueOnError('loading', false)
  })
});
