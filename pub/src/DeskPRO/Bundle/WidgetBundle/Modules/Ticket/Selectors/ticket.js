import { createSelector } from 'reselect';

const stateSelector = state => state.Ticket.ticket;

export const contentSelector = createSelector(
  stateSelector,
  state => state.get('content')
);

export const contentLoadingSelector = createSelector(
  stateSelector,
  state => state.get('loading')
);
