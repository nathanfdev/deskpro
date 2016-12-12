import { allSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allAccountsSelector = allSelectorFactory('VoiceAccount');
export const isAccountsLoadedSelector = isLoadedCollectionSelectorFactory('VoiceAccount', 'all');
