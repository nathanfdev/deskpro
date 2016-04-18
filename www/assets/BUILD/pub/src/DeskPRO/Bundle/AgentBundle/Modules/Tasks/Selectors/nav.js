import { createSelector } from 'reselect';

const stateSelector = state => state.Tasks.nav;

export const isLoadedSelector = createSelector(
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

export const groupsCountMapSelector = createSelector(
  groupsCountSelector,
  groupsCount => {
    const countMap = [];
    groupsCount.get('nested').forEach(groupCount => {
      countMap[groupCount.get('type')] = parseInt(groupCount.get('count'), 10);
    });

    return countMap;
  }
);

export const agentsCountMapSelector = createSelector(
  agentsCountSelector,
  agentsCount => {
    const countMap = [];
    agentsCount.get('nested').forEach(agentCount => {
      countMap[agentCount.get('id')] = parseInt(agentCount.get('count'), 10);
    });

    return countMap;
  }
);

export const projectsCountMapSelector = createSelector(
  projectsCountSelector,
  projectsCount => {
    const countMap = [];
    projectsCount.get('nested').forEach(projectCount => {
      countMap[projectCount.get('id')] = parseInt(projectCount.get('count'), 10);
    });

    return countMap;
  }
);
