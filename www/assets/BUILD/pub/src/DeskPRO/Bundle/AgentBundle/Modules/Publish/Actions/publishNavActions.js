import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

export const initialLoad = createAction(
  'PUBLISH_NAV_INITIAL_LOAD',
  () => (dispatch, getState) => new Promise(
    (resolve) => {
      const currentArticlesGrouping  = hashStateSelectorFactory(['group', 'articles'], 'category')(getState());
      const currentNewsGrouping      = hashStateSelectorFactory(['group', 'news'], 'category')(getState());
      const currentDownloadsGrouping = hashStateSelectorFactory(['group', 'downloads'], 'category')(getState());
      const batchComponents          = {
        articles:             { endpoint: 'articles/counts', query: `group_by=${currentArticlesGrouping}` },
        news:                 { endpoint: 'news/counts', query: `group_by=${currentNewsGrouping}` },
        downloads:            { endpoint: 'downloads/counts', query: `group_by=${currentDownloadsGrouping}` },
        categories:           { endpoint: 'content_categories' },
        articlesDraftsCount:  { endpoint: 'articles/counts', query: 'status=hidden&hidden_status=draft' },
        articlesPendingCount: { endpoint: 'article_pending_creates/counts' },

        articlesCommentsToValidateCount:  { endpoint: 'article_comments/counts', query: 'status=validating' },
        newsCommentsToValidateCount:      { endpoint: 'news_comments/counts', query: 'status=validating' },
        downloadsCommentsToValidateCount: { endpoint: 'download_comments/counts', query: 'status=validating' },
        articlesCommentsToReviewCount:    { endpoint: 'article_comments/counts', query: 'is_reviewed=0' },
        newsCommentsToReviewCount:        { endpoint: 'news_comments/counts', query: 'is_reviewed=0' },
        downloadsCommentsToReviewCount:   { endpoint: 'download_comments/counts', query: 'is_reviewed=0' }
      };

      const batch = api.prepareParams(batchComponents);

      api.sendGet(batch).success(({ responses }) => {
        const payload = flattenBatchResponses(responses);

        /** @namespace payload.articlesPendingCount */
        /** @namespace payload.articlesCommentsToValidateCount */
        /** @namespace payload.newsCommentsToValidateCount */
        /** @namespace payload.downloadsCommentsToValidateCount */
        /** @namespace payload.articlesCommentsToReviewCount */
        /** @namespace payload.downloadsCommentsToReviewCount */
        /** @namespace payload.newsCommentsToReviewCount */
        /** @namespace payload.articlesDraftsCount */

        Object.assign(payload, {
          todo: {
            articles: {
              draft:   payload.articlesDraftsCount.count,
              pending: payload.articlesPendingCount.count
            },
            comments: {
              validate: {
                articles:  payload.articlesCommentsToValidateCount.count,
                news:      payload.newsCommentsToValidateCount.count,
                downloads: payload.downloadsCommentsToValidateCount.count
              },

              review: {
                articles:  payload.articlesCommentsToReviewCount.count,
                news:      payload.newsCommentsToReviewCount.count,
                downloads: payload.downloadsCommentsToReviewCount.count
              }
            }
          }
        });

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

export const changeListGroupingActionFactory = function(content) {
  return createAction(
    'PUBLISH_NAV_CHANGE_LIST_GROUPING',
    groupBy => dispatch => {
      dispatch(loadCounts(content, groupBy));

      return { content, grouped_by: groupBy };
    }
  );
};

export const setMine = createAction(
  'PUBLISH_NAV_SET_MINE',
  (isMine) => (dispatch) => {
    dispatch(loadDraftsCount(isMine));
    dispatch(loadPendingCount(isMine));
    return isMine;
  }
);
