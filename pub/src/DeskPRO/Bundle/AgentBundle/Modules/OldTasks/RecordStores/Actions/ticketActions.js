import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import * as Tasks from 'DeskPRO/Bundle/AgentBundle/Services/Api/Tasks';

export const releaseTickets = createAction('RELEASE_TICKETS', recordStoreActions.releaseRecords());
export const releaseTicketRequest = createAction('RELEASE_TICKET_REQUEST', recordStoreActions.releaseRequest());
export const setTicketRequest = createAction('SET_TICKETS_REQUEST', recordStoreActions.setRequestRecords());

export const loadTickets = createAction('LOAD_TICKETS', recordStoreActions.requestRecords(['RecordStores', 'OldTasks', 'tickets'], (missingIds) => {
  return new Promise((resolve, reject) => {
    Tasks.loadLinkedTickets({         // @TODO Move to a more appropriate library
      ids: missingIds.join(',')
    })
      .success(response => resolve(response.data))
      .error(response => reject(response));
  });
}));
