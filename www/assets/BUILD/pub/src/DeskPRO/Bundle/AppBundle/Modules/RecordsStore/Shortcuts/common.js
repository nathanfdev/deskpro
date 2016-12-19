import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from '../Selectors/store';

export const myTicketsDepartmentsSelector = collectionSelectorFactory('Department', 'my_tickets');
export const myAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'my');
