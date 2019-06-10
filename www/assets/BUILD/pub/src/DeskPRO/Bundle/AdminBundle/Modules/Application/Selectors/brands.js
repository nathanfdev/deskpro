import { collectionSelectorFactory, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allBrandsSelector = collectionSelectorFactory('Brand', 'all');
export const allBrandsLoadedSelector = isLoadedCollectionSelectorFactory('Brand', 'all');
