import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/ticketActions';
import { async, setFullPayload, setValue, setValueOnError } from 'Ampliflux/reducers/handlers';

const initialState = {
  newForm: {
    content: null,
    loading: false,
    saving: false
  }
};

export default createReducer(initialState, {
  [actions.loadNewTicketForm]: async({
    success: setFullPayload('newForm.content'),
    start: setValue('newForm.loading', true),
    done: setValue('newForm.loading', false),
    error: setValueOnError('newForm.loading', false)
  }),
  [actions.saveNewTicketForm]: async({
    success: setFullPayload('newForm.content'),
    start: setValue('newForm.saving', true),
    done: setValue('newForm.saving', false),
    error: setValueOnError('newForm.saving', false)
  })
});
