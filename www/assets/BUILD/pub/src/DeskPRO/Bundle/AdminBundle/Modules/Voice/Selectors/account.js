import { createSelector } from 'reselect';
import { allSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allAccountsSelector = allSelectorFactory('VoiceAccount');
export const isAccountsLoadedSelector = isLoadedCollectionSelectorFactory('VoiceAccount', 'all');

export const allTwilioAccountsSelector = createSelector(
  allAccountsSelector,
  accounts => accounts.filter(account => account.get('type') === 'twilio')
);
