import { createAction } from 'Ampliflux';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as ArticlePendingCreates from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/ArticlePendingCreates';
import * as Comments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments';
import { currentListParamsSelector } from '../Selectors/list';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';

const recordStoresId = 'publish';

/*
 export const switchContent = createAction(
 'PUBLISH_LIST_SWITCH_CONTENT',
 content => content
 );
 */

export const load = createAction(
  'PUBLISH_LIST_LOAD_DATA',
    params => (dispatch) => Content.load(params).then(promise => {
      const content = promise.getData();
      const people = [];
      for (const key in content.linked.person) {
        if (content.linked.person.hasOwnProperty(key)) {
          people.push(content.linked.person[key]);
        }
      }
      dispatch(setPeopleRequest(recordStoresId, people));

      return { content: params.content, data: content };
    }
  )
);

export const loadDraftArticles = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (mine) => () => {
    const filters = { status: 'hidden', hidden_status: 'draft' };
    if (mine) {
      filters.author = 'me';
    }
    return Content.load('articles', filters)
      .then(promise => {
        return { content: 'draftArticles', elements: promise.getData().data };
      }
    );
  }
);

export const loadPendingArticles = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (mine) => (dispatch) => ArticlePendingCreates.load(mine ? 'me' : null)
    .then(promise => {
      // dispatch(switchContent('pendingArticles'));
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
        // dispatch(switchContent('commentsToValidate'));
        return { content: 'commentsToValidate', elements: promise.getData().data };
      }
    );
  }
);

export const loadCommentsToReview = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  () => (dispatch) => Comments.load('articles', { is_reviewed: 0 })
    .then(promise => {
      // dispatch(switchContent('commentsToReview'));
      return { content: 'commentsToReview', elements: promise.getData().data };
    }
  )
);

export const setParams = createAction('PUBLISH_LIST_SET_CURRENT_PARAMS');

export const applyParams = createAction(
  'PUBLISH_APPLY_LIST_PARAMS',
  (overwrite = {}) => (dispatch, getState) => {
    const current = currentListParamsSelector(getState()).toJS();
    const params = { ...current, ...overwrite };
    const { delayReload } = params;
    if (!overwrite.hasOwnProperty('page') && current.hasOwnProperty('page')) {
      delete params.page;
    }
    delete params.delayReload;
    dispatch(setParams(params));
    if (params.content && !delayReload) {
      dispatch(load(params));
    }
  }
);

export const setSort = createAction(
  'PUBLISH_LIST_SET_SORT',
    sort => dispatch => dispatch(applyParams({ sort, delayReload: true }))
);
export const setOrder = createAction(
  'PUBLISH_LIST_SET_ORDER',
    order => dispatch => dispatch(applyParams({ order, delayReload: true }))
);

