import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from './store';

export const myDepartmentsSelector = collectionSelectorFactory('Department', 'my');
export const myAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'my');
