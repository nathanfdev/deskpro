import { createAction } from "Ampliflux";
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as ArticlePendingCreates from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/ArticlePendingCreates';
import * as Comments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments';

export const load = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (trigger, content, groupBy, group) => Content.load(content, {[groupBy]: group}).then(
    promise => {
      trigger({content, elements: promise.getData().data});
      trigger(switchContent(content));
    }
  )
);

export const loadDraftArticles = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (trigger, mine) => {
    const filters = {status: 'hidden', hidden_status: 'draft'};
    if (mine) {
      filters.author = 'me';
    }

    return Content.load('articles', filters).then(
      promise => {
        trigger({content: 'draftArticles', elements: promise.getData().data});
        trigger(switchContent('draftArticles'));
      }
    );
  }
);

export const loadPendingArticles = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (trigger, mine) => ArticlePendingCreates.load(mine ? 'me' : null).then(
    promise => {
      trigger({content: 'pendingArticles', elements: promise.getData().data});
      trigger(switchContent('pendingArticles'));
    }
  )
);

export const loadCommentsToValidate = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (trigger, groupBy, group) => {
    const filters = {status: 'validating'};
    if (groupBy && group) {
      filters[groupBy] = group;
    }

    return Comments.load('articles', filters).then(
        promise => {
        trigger({content: 'commentsToValidate', elements: promise.getData().data});
        trigger(switchContent('commentsToValidate'));
      }
    )
  }
);

export const loadCommentsToReview = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (trigger) => Comments.load('articles', {is_reviewed: 1}).then(
    promise => {
      trigger({content: 'commentsToReview', elements: promise.getData().data});
      trigger(switchContent('commentsToReview'));
    }
  )
);

export const switchContent = createAction(
  'PUBLISH_LIST_SWITCH_CONTENT',
  (trigger, content) => trigger(content)
);

export const toggleView = createAction(
  'PUBLISH_LIST_TOGGLE_VIEW'
);
