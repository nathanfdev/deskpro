import { createSelector } from 'reselect';
import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allNumbersSelector = collectionSelectorFactory('TwilioNumber', 'all');
export const isNumbersLoadedSelector = isLoadedCollectionSelectorFactory('TwilioNumber', 'all');

const stateSelector = state => state.Twilio.numbers;

export const existingNumbersFilterSelector = createSelector(
  stateSelector,
  state => state.get('existingFilter').toJS()
);

export const availableNumbersFilterSelector = createSelector(
  stateSelector,
  state => state.get('availableFilter').toJS()
);

export const expandedNumberSelector = createSelector(
  stateSelector,
  state => state.get('expandedNumber')
);
