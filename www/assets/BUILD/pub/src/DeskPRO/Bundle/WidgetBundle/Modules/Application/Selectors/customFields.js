import { createSelector } from 'reselect';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

const allFieldsSelector = allSelectorFactory('CustomDefChat', 'all');

export const customChatFieldsSelector = createSelector(
  allFieldsSelector,
  fields => fields.filter(field => field.get('is_enabled'))
);
export const customChatFieldsOrderedSelector = createSelector(
  customChatFieldsSelector,
  fields => fields.toOrderedMap().sort((a, b) => a.get('display_order') - b.get('display_order'))
);
