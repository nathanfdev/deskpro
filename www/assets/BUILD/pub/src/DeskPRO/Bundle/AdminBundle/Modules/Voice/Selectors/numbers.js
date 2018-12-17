import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allNumbersSelector = collectionSelectorFactory('VoiceNumber', 'all');
export const isNumbersLoadedSelector = isLoadedCollectionSelectorFactory('VoiceNumber', 'all');
