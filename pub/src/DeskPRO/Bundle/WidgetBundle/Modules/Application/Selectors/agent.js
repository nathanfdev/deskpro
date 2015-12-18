import { createSelector } from 'reselect';

const stateSelector = state => state.Application.agent;

export const onlineAgentsSelector = createSelector(
  stateSelector,
  state => state.get('agents')
);
