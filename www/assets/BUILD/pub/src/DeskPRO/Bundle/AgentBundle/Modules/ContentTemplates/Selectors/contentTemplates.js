import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allContentTemplatesSelector = collectionSelectorFactory('ContentTemplate', 'all');
export const isContentTemplatesLoadedSelector = isLoadedCollectionSelectorFactory('ContentTemplate', 'all');
