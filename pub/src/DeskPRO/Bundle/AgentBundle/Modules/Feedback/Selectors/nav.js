import { createSelector } from 'reselect';
const stateSelector = state => state.Feedback.nav;

export const isDoneSelector = createSelector(
  stateSelector,
    state => state.getIn(['async', 'done'])
);
