import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allVoiceMissedAgentCallsSelector = collectionSelectorFactory('VoiceMissedAgentCall', 'all');
export const isVoiceMissedAgentCallsLoadedSelector = isLoadedCollectionSelectorFactory('VoiceMissedAgentCall', 'all');
