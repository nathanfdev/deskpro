import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { currentListParamsSelector } from '../Selectors/list';
import { toggleMassAction } from '../../Application/Actions/massActions';

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

    return repository('Content').load(params)
      .then(promise => {
        const res = promise.getData();

        switch (params.content) {
          case 'articles':
            dispatch(setCollection('Article', recordStoresId, res.data));
            break;
          case 'news':
            dispatch(setCollection('News', recordStoresId, res.data));
            break;
          case 'downloads':
            dispatch(setCollection('Download', recordStoresId, res.data));
            break;
          case 'article_comments':
            dispatch(setCollection('ArticleComment', recordStoresId, res.data));
            dispatch(setCollection('Article', recordStoresId, prepareLinkedData(res.linked.article)));
            break;
          case 'download_comments':
            dispatch(setCollection('DownloadComment', recordStoresId, res.data));
            dispatch(setCollection('Download', recordStoresId, prepareLinkedData(res.linked.download)));
            break;
          case 'news_comments':
            dispatch(setCollection('NewsComment', recordStoresId, res.data));
            dispatch(setCollection('News', recordStoresId, prepareLinkedData(res.linked.news)));
            break;
          case 'article_pending_creates':
            dispatch(setCollection('ArticlePendingCreate', recordStoresId, res.data));
            break;
          default:
        }
        dispatch(setCollection('Person', recordStoresId, prepareLinkedData(res.linked.person)));
        dispatch(toggleMassAction());

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
