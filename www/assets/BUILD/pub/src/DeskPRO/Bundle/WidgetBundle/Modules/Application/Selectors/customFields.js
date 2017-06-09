import { createSelector } from 'reselect';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { chatBrandCustomFieldsSelector } from './dpWindow';

const allFieldsSelector = allSelectorFactory('CustomDefChat', 'all');
const geBrandField = (brandFields, id) => brandFields.filter(brandField => brandField.get('id') === id).first();

export const customChatFieldsSelector = createSelector(
  allFieldsSelector,
  chatBrandCustomFieldsSelector,
  (fields, brandFields) => fields.filter((field) => {
    const brandField = geBrandField(brandFields, field.get('id'));
    if (!brandField) {
      return false;
    }

    return field.get('is_enabled') && brandField.get('is_enabled');
  })
);
export const customChatFieldsOrderedSelector = createSelector(
  customChatFieldsSelector,
  chatBrandCustomFieldsSelector,
  (fields, brandFields) => fields.toOrderedMap().sort((a, b) => {
    const brandA = geBrandField(brandFields, a.get('id'));
    const brandB = geBrandField(brandFields, b.get('id'));

    return brandA.get('display_order') - brandB.get('display_order');
  })
);
