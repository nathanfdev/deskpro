import { createReducer } from 'Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/snippetsActions';

export const snippets = {
  snippets: []
};

export default createReducer(snippets, {
  [actions.loadTicketSnippets]: async({
    success: setFullPayload('snippets')
  })
});
