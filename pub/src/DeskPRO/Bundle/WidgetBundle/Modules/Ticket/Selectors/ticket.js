import { createSelector } from 'reselect';

const stateSelector = state => state.Ticket.ticket;

export const contentSelector = createSelector(
  stateSelector,
  state => state.getIn(['newForm', 'content'])
);

export const contentLoadingSelector = createSelector(
  stateSelector,
  state => state.getIn(['newForm', 'loading'])
);

export const contentSavingSelector = createSelector(
  stateSelector,
  state => state.getIn(['newForm', 'saving'])
);
