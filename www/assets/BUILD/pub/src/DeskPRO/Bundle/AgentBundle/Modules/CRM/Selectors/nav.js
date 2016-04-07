import { createSelector } from 'reselect';

const stateSelector = state => state.CRM.nav;

export const isLoadedSelector = createSelector(
  stateSelector,
  list => list.getIn(['async', 'done'])
);

export const usersSelector = createSelector(
  stateSelector,
  state => state.get('users')
);

export const organizationsSelector = createSelector(
  stateSelector,
  state => state.get('organizations')
);

export const agentsSelector = createSelector(
  stateSelector,
  (state) => state.get('agents')
);

export const labelsSelector = createSelector(
  stateSelector,
  state => state.get('labels')
);
