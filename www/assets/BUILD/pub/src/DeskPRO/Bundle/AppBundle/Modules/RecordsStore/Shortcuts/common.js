import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from '../Selectors/store';

export const myTicketsDepartmentsSelector = collectionSelectorFactory('Department', 'my_tickets');
export const myDepartmentsSelector = collectionSelectorFactory('Department', 'my');
export const myAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'my');
