import { createSelector } from 'reselect';

const stateSelector = state => state.Ticket.ticket;

export const newTicketFormSelector = createSelector(
  stateSelector,
  state => state.get('newForm')
);

export const bootstrapSelector = createSelector(
  newTicketFormSelector,
  newTicket => newTicket.get('bootstrap')
);

export const contentSelector = createSelector(
  newTicketFormSelector,
  newTicket => newTicket.get('content')
);

export const contentLoadingSelector = createSelector(
  newTicketFormSelector,
  newTicket => newTicket.get('loading')
);

export const contentSavingSelector = createSelector(
  newTicketFormSelector,
  newTicket => newTicket.get('saving')
);
