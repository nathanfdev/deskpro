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

export const projectsCountMapSelector = createSelector(
  projectsCountSelector,
  projectsCount => {
    const countMap = [];
    projectsCount.forEach(projectCount => {
      countMap[projectCount.get('project_id')] = parseInt(projectCount.get('tasks_count'), 10);
    });

    return countMap;
  }
);
