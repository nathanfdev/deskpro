import { createAction } from 'Ampliflux';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as ArticlePendingCreates from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/ArticlePendingCreates';
import * as Comments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments';

export const switchContent = createAction(
  'PUBLISH_LIST_SWITCH_CONTENT',
    content => content
);

export const load = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (content, groupBy, group) => (dispatch) => Content.load(content, { [groupBy]: group })
    .then(promise => {
      dispatch(switchContent(content));
      return { content, elements: promise.getData().data };
    }
  )
);

export const loadDraftArticles = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (mine) => (dispatch) => {
    const filters = { status: 'hidden', hidden_status: 'draft' };
    if (mine) {
      filters.author = 'me';
    }

    return Content.load('articles', filters)
      .then(promise => {
        dispatch(switchContent('draftArticles'));
        return { content: 'draftArticles', elements: promise.getData().data };
      }
    );
  }
);

export const loadPendingArticles = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (mine) => (dispatch) => ArticlePendingCreates.load(mine ? 'me' : null)
    .then(promise => {
      dispatch(switchContent('pendingArticles'));
      return { content: 'pendingArticles', elements: promise.getData().data };
    }
  )
);

export const loadCommentsToValidate = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (groupBy, group) => (dispatch) => {
    const filters = { status: 'validating' };
    if (groupBy && group) {
      filters[groupBy] = group;
    }

    return Comments.load('articles', filters)
      .then(promise => {
        dispatch(switchContent('commentsToValidate'));
        return { content: 'commentsToValidate', elements: promise.getData().data };
      }
    );
  }
);

export const loadCommentsToReview = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  () => (dispatch) => Comments.load('articles', { is_reviewed: 1 })
    .then(promise => {
      dispatch(switchContent('commentsToReview'));
      return { content: 'commentsToReview', elements: promise.getData().data };
    }
  )
);

export const toggleView = createAction(
  'PUBLISH_LIST_TOGGLE_VIEW'
);
