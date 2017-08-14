import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const allSnippetsSelector = collectionSelectorFactory('Snippets', 'all');
export const allSnippetLabelsSelector = collectionSelectorFactory('SnippetLabels', 'all');
export const allSnippetBlobsSelector = collectionSelectorFactory('SnippetBlobs', 'all');
