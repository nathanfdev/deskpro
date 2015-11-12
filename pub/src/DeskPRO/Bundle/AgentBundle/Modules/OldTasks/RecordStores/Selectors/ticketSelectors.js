import { createSelector } from 'reselect';
import { reduceMapToProperty } from 'DeskPRO/Component/Util/Map';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

// You need to define this top-level
// selector that fetches record-store state
// from the global app state
function appTicketStateSel(state) {
  return state.RecordStores.OldTasks.tickets;
}

// This creates a number of selectors for records, status and requests
// This is not typically used by client-code (usually only used by the next builder)
export const ticketStateSelector = createStoreSelectors(appTicketStateSel);

// This create a number of useful selectors that your client-code will find useful
export const createTicketRequestSelectors = createRequestSelectorsBuilder(ticketStateSelector);

export const ticketNamesSelector = createSelector(
  createTicketRequestSelectors('tickets').recordsSel,
  tickets => reduceMapToProperty('subject', tickets.toJS())
);
