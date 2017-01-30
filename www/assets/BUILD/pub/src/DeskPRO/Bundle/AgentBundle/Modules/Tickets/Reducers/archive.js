import { createReducer } from 'Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/archiveActions';

export const ticketArchiveInitialState = {
  files: {}
};

export default createReducer(ticketArchiveInitialState, {
  [actions.loadFiles]: async({
    success: setFullPayload('files')
  })
});
