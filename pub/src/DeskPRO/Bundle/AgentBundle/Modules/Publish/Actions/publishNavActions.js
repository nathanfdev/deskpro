import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as ArticlePendingCreates from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/ArticlePendingCreates';
import * as Comments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';

export const initialLoad = createAction(
  'PUBLISH_NAV_INITIAL_LOAD',
  () => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch'
          + '?get[articles]=DP_API/articles/counts?group_by%3Dcategory'
          + '&get[news]=DP_API/news/counts?group_by%3Dcategory'
          + '&get[downloads]=DP_API/downloads/counts?group_by%3Dcategory'
          + '&get[content_categories]=DP_API/content_categories'
          + '&get[articlesDraftsCount]=DP_API/articles/counts?status%3Dhidden&hidden_status%3Ddraft&author%3Dme'
          + '&get[articlesPendingCount]=DP_API/article_pending_create/counts?assigned_person%3Dme'
          + '&get[toValidateCount]=DP_API/article_comments/counts?group_by%3Dperiod_created&status%3Dvalidating'
          + '&get[commentsToReviewCount]=DP_API/article_comments/counts?is_reviewed%3D0'
        ;

      DpApi.sendGet(batch).success(({responses}) => {
        const payload = flattenBatchResponses(responses);
        payload.lists = { todo: { articles: {}, comments: {} } };
        payload.lists.articles = payload.articles;
        payload.lists.news = payload.news;
        payload.lists.downloads = payload.downloads;
        payload.lists.todo.articles.draft = payload.articlesDraftsCount.count;
        payload.lists.todo.articles.pending = payload.articlesPendingCount.count;
        payload.lists.todo.comments.validate = payload.toValidateCount;
        payload.lists.todo.comments.review = payload.commentsToReviewCount.count;
        delete payload.articles;
        delete payload.news;
        delete payload.downloads;
        delete payload.articlesDraftsCount;
        delete payload.articlesPendingCount;
        delete payload.toValidateCount;
        delete payload.commentsToReviewCount;
        resolve(payload);
      });
    }
  )
);

export const loadAuthorName = createAction(
  'PUBLISH_NAV_LOAD_AUTHOR_NAME',
  (id) => People.loadPerson(id)
    .then(promise => {
      return { id, name: promise.getData().data.name };
    })
);

export const loadCounts = createAction(
  'PUBLISH_NAV_LOAD_CONTENT_COUNTS',
  (content, groupBy) => (dispatch) => Content.loadCounts(content, groupBy).then(promise => {
    const counts = promise.getData().data;

    if (groupBy === 'author') {
      counts.nested.forEach(count => dispatch(loadAuthorName(count.group)));
    }

    return { content, counts };
  })
);

export const loadDraftsCount = createAction(
  'PUBLISH_NAV_LOAD_DRAFTS_COUNT',
  (mine) => Content.loadDraftsCount('articles', mine ? 'me' : null)
    .then(promise => promise.getData().data.count)
);

export const loadPendingCount = createAction(
  'PUBLISH_NAV_LOAD_PENDING_COUNT',
  (mine) => ArticlePendingCreates.loadCount(mine ? 'me' : null)
    .then(promise => promise.getData().data.count)
);

export const loadCommentsToValidateCounts = createAction(
  'PUBLISH_NAV_LOAD_COMMENTS_TO_VALIDATE_COUNTS',
  () => Comments.loadCommentsToValidateCounts('articles')
    .then(promise => promise.getData().data)
);

export const loadCommentsToReviewCount = createAction(
  'PUBLISH_NAV_LOAD_COMMENTS_TO_REVIEW_COUNT',
  () => Comments.loadCommentsToReviewCount('articles')
    .then(promise => promise.getData().data.count)
);

export const loadCategories = createAction(
  'PUBLISH_NAV_LOAD_CATEGORIES',
  () => Content.loadCategories().then(promise => promise.getData().data)
);

export const toggleListGroupingVisibility = createAction(
  'PUBLISH_NAV_TOGGLE_LIST_GROUPING_VISIBILITY',
    list => list
);

export const changeListGrouping = createAction(
  'PUBLISH_NAV_CHANGE_LIST_GROUPING',
  (list, groupBy) => (dispatch) => {
    dispatch(loadCounts(list, groupBy));
    dispatch(toggleListGroupingVisibility(list));
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