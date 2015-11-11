import { createSelector } from 'reselect';

const stateSelector = state => state.Tasks.nav;

export const isDoneSelector = createSelector(
  stateSelector,
    state => state.getIn(['async', 'done'])
);
