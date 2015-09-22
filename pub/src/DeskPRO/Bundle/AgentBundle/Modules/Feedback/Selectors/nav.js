import { createSelector } from 'reselect';

const stateSelector = state => state.Feedback.nav;

export const groupDataSelector = createSelector(
  stateSelector,
    nav => nav.get('groups').toJS().find(option=> option.current === true)
);