import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allQueuesSelector = collectionSelectorFactory('TwilioQueue', 'all');
export const isQueuesLoadedSelector = isLoadedCollectionSelectorFactory('TwilioQueue', 'all');
