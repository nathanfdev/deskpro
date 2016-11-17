import { createSelector } from 'reselect';

const stateSelector = state => state.Agent.agents;

export const onlineAgentsSelector = createSelector(
  stateSelector,
  state => state.get('onlineAgents')
);
