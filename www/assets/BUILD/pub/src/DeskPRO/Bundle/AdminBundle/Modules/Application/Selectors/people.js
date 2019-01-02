import { createSelector } from 'reselect';
import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allPeopleSelector = collectionSelectorFactory('Person', 'all');
export const allAgentsSelector = collectionSelectorFactory('Person', 'agents');
export const isAgentsLoadedSelector = isLoadedCollectionSelectorFactory('Person', 'agents');
export const allAgentGroupsSelector = collectionSelectorFactory('AgentGroup', 'all');

export const voicePeopleSelector = createSelector(
  allAgentsSelector,
  agents => agents.filter(agent => agent.getIn(['agent_data', 'is_voice_enabled']))
);

export const allAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'all');
export const isAgentTeamsLoadedSelector = isLoadedCollectionSelectorFactory('AgentTeam', 'all');
export const isAgentGroupsLoadedSelector = isLoadedCollectionSelectorFactory('AgentGroup', 'all');
