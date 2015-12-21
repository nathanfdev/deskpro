import { createSelector } from 'reselect';
import Immutable from 'immutable';

const stateSelector = state => state.Application.agent;

export const onlineAgentsSelector = createSelector(
  stateSelector,
  state => state.get('agents') || Immutable.fromJS({})
);

export const onlineAgentsCountSelector = createSelector(
  onlineAgentsSelector,
  onlineAgents => onlineAgents.size
);

export const primaryAgentSelector = createSelector(
  onlineAgentsSelector,
  onlineAgents => onlineAgents.first()
);
