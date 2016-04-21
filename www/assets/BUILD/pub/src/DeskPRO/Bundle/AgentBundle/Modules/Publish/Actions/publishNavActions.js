import { createAction } from 'Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';

export const initialLoad = createAction(
  'PUBLISH_NAV_INITIAL_LOAD',
  () => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch'
          + '?get[articles]=DP_API/articles/counts?group_by%3Dcategory'
          + '&get[news]=DP_API/news/counts?group_by%3Dcategory'
          + '&get[downloads]=DP_API/downloads/counts?group_by%3Dcategory'
          + '&get[categories]=DP_API/content_categories'
          + '&get[articlesDraftsCount]=DP_API/articles/counts?status%3Dhidden%26hidden_status%3Ddraft'
          + '&get[articlesPendingCount]=DP_API/article_pending_creates/counts'
          + '&get[articlesCommentsToValidateCount]=DP_API/article_comments/counts?status%3Dvalidating'
          + '&get[newsCommentsToValidateCount]=DP_API/news_comments/counts?status%3Dvalidating'
          + '&get[downloadsCommentsToValidateCount]=DP_API/download_comments/counts?status%3Dvalidating'
          + '&get[articlesCommentsToReviewCount]=DP_API/article_comments/counts?is_reviewed%3D0'
          + '&get[newsCommentsToReviewCount]=DP_API/news_comments/counts?is_reviewed%3D0'
          + '&get[downloadsCommentsToReviewCount]=DP_API/download_comments/counts?is_reviewed%3D0'
        ;

      api.sendGet(batch).success(({responses}) => {
        const payload = flattenBatchResponses(responses);
        payload.todo = { articles: {}, comments: { validate: {}, review: {} } };
        payload.todo.articles.draft = payload.articlesDraftsCount.count;
        payload.todo.articles.pending = payload.articlesPendingCount.count;
        payload.todo.comments.validate.articles = payload.articlesCommentsToValidateCount.count;
        payload.todo.comments.validate.news = payload.newsCommentsToValidateCount.count;
        payload.todo.comments.validate.downloads = payload.downloadsCommentsToValidateCount.count;
        payload.todo.comments.review.articles = payload.articlesCommentsToReviewCount.count;
        payload.todo.comments.review.news = payload.newsCommentsToReviewCount.count;
        payload.todo.comments.review.downloads = payload.downloadsCommentsToReviewCount.count;
        delete payload.articlesDraftsCount;
        delete payload.articlesPendingCount;
        delete payload.articlesCommentsToValidateCount;
        delete payload.newsCommentsToValidateCount;
        delete payload.downloadsCommentsToValidateCount;
        delete payload.articlesCommentsToReviewCount;
        delete payload.newsCommentsToReviewCount;
        delete payload.downloadsCommentsToReviewCount;
        resolve(payload);
      });
    }
  )
);

export const loadCounts = createAction(
  'PUBLISH_NAV_LOAD_CONTENT_COUNTS',
  (content, groupBy) => repository('Content').loadCounts(content, groupBy).then(promise => {
    const counts = promise.getData().data;

    return { content, counts };
  })
);

export const loadDraftsCount = createAction(
  'PUBLISH_NAV_LOAD_DRAFTS_COUNT',
  (mine) => repository('Content').loadDraftsCount('articles', mine ? 'me' : null)
    .then(promise => promise.getData().data.count)
);

export const loadPendingCount = createAction(
  'PUBLISH_NAV_LOAD_PENDING_COUNT',
  (mine) => repository('ArticlePendingCreate').loadCount(mine ? 'me' : null)
    .then(promise => promise.getData().data.count)
);

export const loadCommentsToValidateCounts = createAction(
  'PUBLISH_NAV_LOAD_COMMENTS_TO_VALIDATE_COUNTS',
  () => repository('Comment').loadCommentsToValidateCounts('articles')
    .then(promise => promise.getData().data)
);

export const loadCommentsToReviewCount = createAction(
  'PUBLISH_NAV_LOAD_COMMENTS_TO_REVIEW_COUNT',
  () => repository('Comment').loadCommentsToReviewCount('articles')
    .then(promise => promise.getData().data.count)
);

export const loadCategories = createAction(
  'PUBLISH_NAV_LOAD_CATEGORIES',
  () => repository('Content').loadCategories().then(promise => promise.getData().data)
);

export const changeListGrouping = createAction(
  'PUBLISH_NAV_CHANGE_LIST_GROUPING',
  (list, groupBy) => (dispatch) => {
    dispatch(loadCounts(list, groupBy));
    return { content: list, grouped_by: groupBy };
  }
);

export const setMine = createAction(
  'PUBLISH_NAV_SET_MINE',
  (isMine) => (dispatch) => {
    dispatch(loadDraftsCount(isMine));
    dispatch(loadPendingCount(isMine));
    return isMine;
  }
);