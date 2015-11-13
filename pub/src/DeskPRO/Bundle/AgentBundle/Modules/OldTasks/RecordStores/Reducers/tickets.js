import * as actions from '../Actions/ticketActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: actions.releaseTickets,
    releaseRequestAction: actions.releaseTicketRequest,
    setRequestRecordAction: actions.setTicketRequest,
    requestRecordsAction: actions.loadTickets
  })
);
