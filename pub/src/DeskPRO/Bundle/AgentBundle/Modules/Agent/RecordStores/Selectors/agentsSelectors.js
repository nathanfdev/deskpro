import { createSelector } from 'reselect';
import { reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

export const agentsSelector = createSelector(
  createPeopleRequestSelectors('agents').recordsSel,
  agents => agents
);

export const agentNamesSelector = createSelector(
  agentsSelector,
  agents => reduceImmutableToProperty('name', agents)
);
