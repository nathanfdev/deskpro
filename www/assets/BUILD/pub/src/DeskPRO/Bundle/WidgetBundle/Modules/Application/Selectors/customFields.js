import { createSelector } from 'reselect';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const customChatFieldsSelector = allSelectorFactory('CustomDefChat', 'all');
export const customChatFieldsOrderedSelector = createSelector(
  customChatFieldsSelector,
  fields => fields.toOrderedMap().sort((a, b) => a.get('display_order') - b.get('display_order'))
);
