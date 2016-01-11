import { createAction } from 'Ampliflux';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { currentListParamsSelector } from '../Selectors/list';
import { setArticlesRequest } from '../RecordStores/Actions/articlesActions';
import { setNewsRequest } from '../RecordStores/Actions/newsActions';
import { setDownloadsRequest } from '../RecordStores/Actions/downloadsActions';
import { setArticlesCommentsRequest } from '../RecordStores/Actions/articlesCommentsActions';
import { setNewsCommentsRequest } from '../RecordStores/Actions/newsCommentsActions';
import { setDownloadsCommentsRequest } from '../RecordStores/Actions/downloadsCommentsActions';
import { setArticlePendingCreatesRequest } from '../RecordStores/Actions/articlePendingCreatesActions.js';

const recordStoresId = 'publish';

const prepareLinkedData = (linked) => {
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
    if (params.hasOwnProperty('date_filter')) {
      const dateFilter = params.date_filter;
      delete params.date_filter;
      params = { ...dateFilter, ...params };
    }

    return Content.load(params)
      .then(promise => {
        const res = promise.getData();

        switch (params.content) {
          case 'articles':
            dispatch(setArticlesRequest(recordStoresId, res.data));
            break;
          case 'news':
            dispatch(setNewsRequest(recordStoresId, res.data));
            break;
          case 'downloads':
            dispatch(setDownloadsRequest(recordStoresId, res.data));
            break;
          case 'article_comments':
            dispatch(setArticlesCommentsRequest(recordStoresId, res.data));
            dispatch(setArticlesRequest(recordStoresId, prepareLinkedData(res.linked.article)));
            break;
          case 'download_comments':
            dispatch(setDownloadsCommentsRequest(recordStoresId, res.data));
            dispatch(setDownloadsRequest(recordStoresId, prepareLinkedData(res.linked.download)));
            break;
          case 'news_comments':
            dispatch(setNewsCommentsRequest(recordStoresId, res.data));
            dispatch(setNewsRequest(recordStoresId, prepareLinkedData(res.linked.news)));
            break;
          case 'article_pending_creates':
            dispatch(setArticlePendingCreatesRequest(recordStoresId, res.data));
            break;
          default:
        }
        dispatch(setPeopleRequest(recordStoresId, prepareLinkedData(res.linked.person)));
        const ids = res.data.map(item=>item.id);

        return { ids: ids, pagination: res.meta.pagination };
      }
    );
  }
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
