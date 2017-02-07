import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allVoicemailRecordsSelector = collectionSelectorFactory('VoicemailRecord', 'all');
export const isVoicemailRecordsLoadedSelector = isLoadedCollectionSelectorFactory('VoicemailRecord', 'all');
