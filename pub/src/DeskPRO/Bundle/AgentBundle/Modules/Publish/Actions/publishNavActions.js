import { createAction } from 'Ampliflux/actions';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';

export const loadArticlesCounts = createAction(
  'PUBLISH_NAV_LOAD_ARTICLES_COUNTS',
  (trigger, groupBy) => Content.loadArticlesCounts(groupBy).then(promise => trigger(promise.getData().data))
);

export const loadNewsCounts = createAction(
  'PUBLISH_NAV_LOAD_NEWS_COUNTS',
  (trigger, groupBy) => Content.loadNewsCounts(groupBy).then(promise => trigger(promise.getData().data))
);

export const loadDownloadsCounts = createAction(
  'PUBLISH_NAV_LOAD_DOWNLOADS_COUNTS',
  (trigger, groupBy) => Content.loadDownloadsCounts(groupBy).then(promise => trigger(promise.getData().data))
);

export const loadCategories = createAction(
  'PUBLISH_NAV_LOAD_CATEGORIES',
  trigger => Content.loadCategories().then(promise => trigger(promise.getData().data))
);
