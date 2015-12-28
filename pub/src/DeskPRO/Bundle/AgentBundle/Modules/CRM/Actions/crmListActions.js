import { createAction } from 'Ampliflux';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import { currentListParamsSelector } from '../Selectors/list';

// const recordStoresId = 'crm';
export const setParams = createAction('CRM_LIST_SET_CURRENT_PARAMS');

export const load = createAction(
  'CRM_LIST_LOAD_DATA',
  (listParams) => {
    let params = listParams;
    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }
    return People.loadPeople(params)
      .then(promise => {
        const data = promise.getData();
        return { content: params.content, data: data };
      }
    );
  }
);

export const applyParams = createAction(
  'CRM_APPLY_LIST_PARAMS',
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


