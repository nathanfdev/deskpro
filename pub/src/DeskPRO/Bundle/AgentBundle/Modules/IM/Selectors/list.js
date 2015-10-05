import { createSelector } from 'reselect';

const stateSelector = state => state.IM.list;

export const getRecentAgents = createSelector(
  stateSelector,
    list => list.get('recentAgents') ? list.get('recentAgents') : []
);