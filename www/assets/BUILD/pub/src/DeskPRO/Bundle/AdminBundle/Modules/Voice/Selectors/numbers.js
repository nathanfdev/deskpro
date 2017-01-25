import { createSelector } from 'reselect';
import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allNumbersSelector = collectionSelectorFactory('VoiceNumber', 'all');
export const isNumbersLoadedSelector = isLoadedCollectionSelectorFactory('VoiceNumber', 'all');

const stateSelector = state => state.Voice.numbers;

export const existingNumbersFilterSelector = createSelector(
  stateSelector,
  state => state.get('existingFilter').toJS()
);

export const availableNumbersFilterSelector = createSelector(
  stateSelector,
  state => state.get('availableFilter').toJS()
);
