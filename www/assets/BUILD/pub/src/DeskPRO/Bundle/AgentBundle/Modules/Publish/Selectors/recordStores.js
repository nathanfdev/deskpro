import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const articlesSelector              = collectionSelectorFactory('Article', 'publish');
export const newsSelector                  = collectionSelectorFactory('News', 'publish');
export const downloadsSelector             = collectionSelectorFactory('Download', 'publish');
export const articlePendingCreatesSelector = collectionSelectorFactory('ArticlePendingCreate', 'publish');
export const articlesCommentsSelector      = collectionSelectorFactory('ArticleComment', 'publish');
export const newsCommentsSelector          = collectionSelectorFactory('NewsComment', 'publish');
export const downloadsCommentsSelector     = collectionSelectorFactory('DownloadComment', 'publish');
