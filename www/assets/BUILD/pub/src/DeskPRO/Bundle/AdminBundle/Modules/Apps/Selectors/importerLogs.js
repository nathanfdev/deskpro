import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allImporterLogsSelector = collectionSelectorFactory('ImportLog', 'all');
export const isImporterLogsLoadedSelector = isLoadedCollectionSelectorFactory('ImportLog', 'all');
