import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/ticketActions';
import { async, setFullPayload, setValue, setValueOnError } from 'Ampliflux/reducers/handlers';

const initialState = {
  content: null,
  loading: false,
  saving: false
};

export default createReducer(initialState, {
  [actions.loadForm]: async({
    success: setFullPayload('content'),
    start: setValue('loading', true),
    done: setValue('loading', false),
    error: setValueOnError('loading', false)
  }),
  [actions.saveForm]: async({
    success: setFullPayload('content'),
    start: setValue('saving', true),
    done: setValue('saving', false),
    error: setValueOnError('saving', false)
  })
});
