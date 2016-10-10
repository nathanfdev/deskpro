import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allAgentsSelector = collectionSelectorFactory('Person', 'agents');
export const isAgentsLoadedSelector = isLoadedCollectionSelectorFactory('Person', 'agents');
