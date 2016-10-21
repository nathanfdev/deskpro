import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allAutoAttendantsSelector = collectionSelectorFactory('VoiceAutoAttendant', 'all');
export const isAutoAttendantsLoadedSelector = isLoadedCollectionSelectorFactory('VoiceAutoAttendant', 'all');
