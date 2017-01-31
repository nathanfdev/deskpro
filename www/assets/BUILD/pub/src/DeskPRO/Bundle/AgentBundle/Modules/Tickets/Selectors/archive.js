import { createSelector } from 'reselect';

const stateSelector = state => state.Tickets.archive;

export const filesSelector = createSelector(
  stateSelector,
  state => state.get('files')
);
