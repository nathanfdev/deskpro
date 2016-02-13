import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from '../Selectors/store';

export const myDepartmentsSelector = collectionSelectorFactory('Department', 'my');
export const myAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'my');
