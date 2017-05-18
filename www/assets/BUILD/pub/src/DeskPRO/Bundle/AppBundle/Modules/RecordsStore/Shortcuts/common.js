import { createSelector } from 'reselect';
import { collectionSelectorFactory } from '../Selectors/store';

export const myTicketsDepartmentsSelector = collectionSelectorFactory('Department', 'my_tickets');
export const myAgentTeamsSelector = collectionSelectorFactory('AgentTeam', 'my');

const defaultBrandStateSelector = collectionSelectorFactory('Brand', 'default');
export const defaultBrandSelector =  createSelector(defaultBrandStateSelector, brands => brands.first());
