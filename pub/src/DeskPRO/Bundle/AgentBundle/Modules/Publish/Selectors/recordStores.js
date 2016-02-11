import { createSelector } from 'reselect';
import { createArticlesRequestSelectors } from '../RecordStores/Selectors/articlesSelectors';
import { createNewsRequestSelectors } from '../RecordStores/Selectors/newsSelectors';
import { createDownloadsRequestSelectors } from '../RecordStores/Selectors/downloadsSelectors';
import { createArticlesCommentsRequestSelectors } from '../RecordStores/Selectors/articlesCommentsSelectors';
import { createNewsCommentsRequestSelectors } from '../RecordStores/Selectors/newsCommentsSelectors';
import { createDownloadsCommentsRequestSelectors } from '../RecordStores/Selectors/downloadsCommentsSelectors';
import { createArticlePendingCreatesRequestSelectors } from '../RecordStores/Selectors/articlePendingCreatesSelectors';

export const articlesSelector = createSelector(
  createArticlesRequestSelectors('publish').recordsSel,
    articles => articles
);

export const newsSelector = createSelector(
  createNewsRequestSelectors('publish').recordsSel,
    news => news
);

export const downloadsSelector = createSelector(
  createDownloadsRequestSelectors('publish').recordsSel,
    downloads => downloads
);

export const articlePendingCreatesSelector = createSelector(
  createArticlePendingCreatesRequestSelectors('publish').recordsSel,
    comments => comments
);

export const articlesCommentsSelector = createSelector(
  createArticlesCommentsRequestSelectors('publish').recordsSel,
    comments => comments
);

export const newsCommentsSelector = createSelector(
  createNewsCommentsRequestSelectors('publish').recordsSel,
    comments => comments
);

export const downloadsCommentsSelector = createSelector(
  createDownloadsCommentsRequestSelectors('publish').recordsSel,
    comments => comments
);
