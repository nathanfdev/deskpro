import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/ticketActions';
import { async, setFullPayload, setValue, setValueOnError } from 'Ampliflux/reducers/handlers';

const initialState = {
  newForm: {
    content:   null,
    bootstrap: false,
    loading:   false,
    saving:    false
  }
};

export default createReducer(initialState, {
  [actions.setNewTicketFormContent]: setFullPayload('newForm.content'),

  [actions.loadNewTicketForm]: async({
    start: setValue('newForm.loading', true),
    done:  setValue('newForm.loading', false),
    error: setValueOnError('newForm.loading', false)
  }),
  [actions.bootstrapTicketApp]: async({
    start: setValue('newForm.bootstrap', true),
    done:  setValue('newForm.bootstrap', false),
    error: setValueOnError('newForm.bootstrap', false)
  }),
  [actions.saveNewTicketForm]: async({
    start: setValue('newForm.saving', true),
    done:  setValue('newForm.saving', false),
    error: setValueOnError('newForm.saving', false)
  })
});
