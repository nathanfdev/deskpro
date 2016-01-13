import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

const storeSelectors = createStoreSelectors(state => state.RecordStores.Application.people);
const selectorBuilder = createRequestSelectorsBuilder(storeSelectors);

export const peopleSelector = storeSelectors.recordsSel;

const onlineAgentsSelectors = selectorBuilder('onlineAgents');
export const onlineAgentsSelector = createSelector(
  onlineAgentsSelectors.recordsSel,
  agents => agents
);

export const onlineAgentsCountSelector = createSelector(
  onlineAgentsSelector,
  agents => agents.size
);

export const primaryAgentSelector = createSelector(
  onlineAgentsSelector,
  agents => agents.first()
);

