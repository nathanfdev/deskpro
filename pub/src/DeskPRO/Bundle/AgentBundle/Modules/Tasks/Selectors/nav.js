import { createSelector } from 'reselect';

const stateSelector = state => state.Tasks.nav;

export const isDoneSelector = createSelector(
  stateSelector,
  state => state.getIn(['async', 'done'])
);

export const agentsCountSelector = createSelector(
  stateSelector,
  state => state.get('agents')
);

export const groupsCountSelector = createSelector(
  stateSelector,
  state => state.get('groups')
);

export const projectsCountSelector = createSelector(
  stateSelector,
  state => state.get('projects')
);