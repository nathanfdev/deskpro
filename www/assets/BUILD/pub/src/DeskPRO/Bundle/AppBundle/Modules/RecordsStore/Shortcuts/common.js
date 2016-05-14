import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from '../Selectors/store';

export const myDepartmentsSelector = collectionSelectorFactory('Department', 'my');
export const myChatsDepartmentsSelector = collectionSelectorFactory('ChatDepartment', 'my');
export const myAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'my');
