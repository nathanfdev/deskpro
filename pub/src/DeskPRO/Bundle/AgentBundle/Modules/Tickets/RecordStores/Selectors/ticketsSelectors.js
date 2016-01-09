import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

function ticketsStateSel(state) {
  return state.RecordStores.Tickets.tickets;
}

export const ticketsStateSelector = createStoreSelectors(ticketsStateSel);
export const createTicketsRequestSelectors = createRequestSelectorsBuilder(ticketsStateSelector);
