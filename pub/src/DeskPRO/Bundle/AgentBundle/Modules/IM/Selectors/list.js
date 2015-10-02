import { createSelector } from 'reselect';

const stateSelector = state => state.IM.list;

export const getRecentAgents = createSelector(
  stateSelector,
    list => list.get('recentAgents') ? list.get('recentAgents') : []
);

export const getAgents = createSelector(
  stateSelector,
    list => list.get('agents') ? list.get('agents') : []
);

export const getTeams = createSelector(
  stateSelector,
    list => list.get('teams') ? list.get('teams') : []
);

export const getDepartments = createSelector(
  stateSelector,
    list => list.get('departments') ? list.get('departments') : []
);