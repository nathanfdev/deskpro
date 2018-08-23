import { createSelector } from 'reselect';
import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allNumbersSelector = collectionSelectorFactory('VoiceNumber', 'all');
export const isNumbersLoadedSelector = isLoadedCollectionSelectorFactory('VoiceNumber', 'all');

export const outboundNumbersSelector = createSelector(
  allNumbersSelector,
  numbers => numbers.filter(number => number.get('outbound_calls_enabled'))
);

export const canOpenDialpadSelector = createSelector(
  outboundNumbersSelector,
  numbers => numbers.size > 0
);
