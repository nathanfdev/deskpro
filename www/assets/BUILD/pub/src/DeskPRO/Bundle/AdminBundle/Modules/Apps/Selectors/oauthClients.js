import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allOAuthClientsSelector = collectionSelectorFactory('OAuthClient', 'all');
export const isOAuthClientsLoadedSelector = isLoadedCollectionSelectorFactory('OAuthClient', 'all');
