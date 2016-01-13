import { createSelector } from 'reselect';
import { createStoreSelectors, createRequestSelectorsBuilder } from 'Ampliflux/common/record-store/selectors';

const storeSelector = createStoreSelectors(state => state.RecordStores.Application.people);
const selectorBuilder = createRequestSelectorsBuilder(storeSelector);

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
