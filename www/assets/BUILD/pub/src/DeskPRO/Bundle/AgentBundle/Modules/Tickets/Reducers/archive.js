import { createReducer } from 'Ampliflux';
import { pushPayloadToCollection, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/archiveActions';

export const ticketArchiveInitialState = {
  files: []
};

export default createReducer(ticketArchiveInitialState, {
  [actions.loadFiles]: async({
    success: pushPayloadToCollection('files')
  })
});
