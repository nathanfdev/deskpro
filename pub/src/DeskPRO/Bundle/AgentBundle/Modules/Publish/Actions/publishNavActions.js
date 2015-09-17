import { createAction } from 'Ampliflux';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as ArticlePendingCreates from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/ArticlePendingCreates';
import * as Comments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';

export const loadCounts = createAction(
  'PUBLISH_NAV_LOAD_CONTENT_COUNTS',
  (trigger, content, groupBy) => Content.loadCounts(content, groupBy).then(promise => {
    const counts = promise.getData().data;

    if (groupBy === 'author') {
      counts.nested.forEach(count => trigger(loadAuthorName(count.group)));
    }

    trigger({content, counts});
  })
);

export const loadDraftsCount = createAction(
  'PUBLISH_NAV_LOAD_DRAFTS_COUNT',
  (trigger, mine) => Content.loadDraftsCount('articles', mine ? 'me' : null).then(
    promise => trigger(promise.getData().data.count)
  )
);

export const loadPendingCount = createAction(
  'PUBLISH_NAV_LOAD_PENDING_COUNT',
  (trigger, mine) => ArticlePendingCreates.loadCount(mine ? 'me' : null).then(
    promise => trigger(promise.getData().data.count)
  )
);

export const loadCommentsToValidateCounts = createAction(
  'PUBLISH_NAV_LOAD_COMMENTS_TO_VALIDATE_COUNTS',
  trigger => Comments.loadCommentsToValidateCounts('articles').then(
    promise => trigger(promise.getData().data)
  )
);

export const loadCommentsToReviewCount = createAction(
  'PUBLISH_NAV_LOAD_COMMENTS_TO_REVIEW_COUNT',
  trigger => Comments.loadCommentsToReviewCount('articles').then(
      promise => trigger(promise.getData().data.count)
  )
);

export const loadAuthorName = createAction(
  'PUBLISH_NAV_LOAD_AUTHOR_NAME',
  (trigger, id) => People.loadPerson(id).then(promise => trigger({id, name: promise.getData().data.name}))
);

export const loadCategories = createAction(
  'PUBLISH_NAV_LOAD_CATEGORIES',
  trigger => Content.loadCategories().then(promise => trigger(promise.getData().data))
);

export const toggleListGroupingVisibility = createAction(
  'PUBLISH_NAV_TOGGLE_LIST_GROUPING_VISIBILITY',
  (trigger, list) => trigger(list)
);

export const changeListGrouping = createAction(
  'PUBLISH_NAV_CHANGE_LIST_GROUPING',
  (trigger, list, groupBy) => {
    trigger(loadCounts(list, groupBy));
    trigger(toggleListGroupingVisibility(list));
  }
);

export const setMine = createAction(
  'PUBLISH_NAV_SET_MINE',
  (trigger, isMine) => {
    trigger(isMine);
    trigger(loadDraftsCount(isMine));
    trigger(loadPendingCount(isMine));
  }
);