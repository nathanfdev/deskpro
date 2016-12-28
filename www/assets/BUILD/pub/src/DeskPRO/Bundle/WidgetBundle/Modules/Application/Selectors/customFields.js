import { createSelector } from 'reselect';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { chatEnabledCustomFieldsSelector } from './dpWindow';

const allFieldsSelector = allSelectorFactory('CustomDefChat', 'all');

export const customChatFieldsSelector = createSelector(
  allFieldsSelector,
  chatEnabledCustomFieldsSelector,
  (fields, enabledIds) => fields.filter(field =>
    field.get('is_enabled') && (!enabledIds || enabledIds.contains(field.get('id'))
  ))
);
export const customChatFieldsOrderedSelector = createSelector(
  customChatFieldsSelector,
  fields => fields.toOrderedMap().sort((a, b) => a.get('display_order') - b.get('display_order'))
);
