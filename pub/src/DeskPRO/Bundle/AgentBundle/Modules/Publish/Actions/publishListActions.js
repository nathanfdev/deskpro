import { createAction } from 'Ampliflux';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as ArticlePendingCreates from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/ArticlePendingCreates';
import * as Comments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments';
import { currentListParamsSelector } from '../Selectors/list';
import { setArticlesRequest } from '../RecordStores/Actions/articlesActions';
import { setNewsRequest } from '../RecordStores/Actions/newsActions';
import { setDownloadsRequest } from '../RecordStores/Actions/downloadsActions';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';

const recordStoresId = 'publish';

const prepareLinkedData = (linked) =>{
  const result = [];
  for (const key in linked) {
    if (linked.hasOwnProperty(key)) {
      result.push(linked[key]);
    }
  }
  return result;
};

export const load = createAction(
  'PUBLISH_LIST_LOAD_DATA',
  (listParams) => dispatch => {
    let params = listParams;
    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }
    return Content.load(params)
      .then(promise => {
        const data = promise.getData();

        switch (params.content) {
          case 'article_comments':
            dispatch(setArticlesRequest(recordStoresId, prepareLinkedData(data.linked.article)));
            break;
          case 'download_comments':
            dispatch(setDownloadsRequest(recordStoresId, prepareLinkedData(data.linked.download)));
            break;
          case 'news_comments':
            dispatch(setNewsRequest(recordStoresId, prepareLinkedData(data.linked.news)));
            break;
          default:
        }
        dispatch(setPeopleRequest(recordStoresId, prepareLinkedData(data.linked.person)));

        return { content: params.content, data: data };
      }
    );
  }
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

