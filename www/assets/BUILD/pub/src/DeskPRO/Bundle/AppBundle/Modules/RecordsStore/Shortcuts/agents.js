import { createSelector } from 'reselect';
import { reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';
import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from '../index';

export const agentsSelector = collectionSelectorFactory('Person', 'agents');
export const isAgentsLoadedSelector = isLoadedCollectionSelectorFactory('Person', 'agents');

export const agentNamesSelector = createSelector(
  agentsSelector,
  agents => reduceImmutableToProperty('name', agents)
);
