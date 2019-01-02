import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allQueuesSelector = collectionSelectorFactory('UserChatQueue', 'all');
export const isQueuesLoadedSelector = isLoadedCollectionSelectorFactory('UserChatQueue', 'all');
