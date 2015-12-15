import { createAction } from 'Ampliflux';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as ArticlePendingCreates from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/ArticlePendingCreates';
import * as Comments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';

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

export const loadAuthorName = createAction(
  'PUBLISH_NAV_LOAD_AUTHOR_NAME',
  (id) => People.loadPerson(id)
    .then(promise => {
      return { id, name: promise.getData().data.name };
    })
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