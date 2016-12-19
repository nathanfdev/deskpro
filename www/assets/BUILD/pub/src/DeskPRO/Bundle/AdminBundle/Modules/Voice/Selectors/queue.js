import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allQueuesSelector = collectionSelectorFactory('VoiceQueue', 'all');
export const isQueuesLoadedSelector = isLoadedCollectionSelectorFactory('VoiceQueue', 'all');
