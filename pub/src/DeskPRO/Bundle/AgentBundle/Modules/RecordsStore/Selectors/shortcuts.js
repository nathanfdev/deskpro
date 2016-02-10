import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from './store';

// Department
export const myDepartmentsSelector = collectionSelectorFactory('Department', 'my');
export const departmentsLoaded = isLoadedCollectionSelectorFactory('Department', 'all');

// AgentTeam
export const myAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'my');
export const agentTeamsLoadedSelector = isLoadedCollectionSelectorFactory('AgentTeam', 'all');
