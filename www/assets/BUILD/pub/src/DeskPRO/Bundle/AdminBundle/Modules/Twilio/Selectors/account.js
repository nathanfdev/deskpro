import { allSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allAccountsSelector = allSelectorFactory('TwilioAccount');
export const isAccountsLoadedSelector = isLoadedCollectionSelectorFactory('TwilioAccount', 'all');
