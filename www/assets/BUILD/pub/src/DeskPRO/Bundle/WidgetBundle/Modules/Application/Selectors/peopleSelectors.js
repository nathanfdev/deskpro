import { createSelector } from 'reselect';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const peopleSelector = function(state) {
  return state.RecordsStore.store.Person.records;
};

export const onlineAgentsSelector = collectionSelectorFactory('Person', 'onlineAgents');

export const onlineAgentsCountSelector = createSelector(
  onlineAgentsSelector,
  agents => agents.size
);

export const primaryAgentSelector = createSelector(
  onlineAgentsSelector,
  agents => agents.first()
);
