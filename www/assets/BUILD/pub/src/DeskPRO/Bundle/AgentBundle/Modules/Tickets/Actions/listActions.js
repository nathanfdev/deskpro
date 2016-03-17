import { createAction } from 'Ampliflux';
import { listParamsSelector } from '../Selectors/list';
import { setCollection, releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

// Private -------------------------------------------------------------------------------------------------------------

const setListParams = createAction('TICKETS_LIST_SET_LIST_PARAMS');
const setPagination = createAction('TICKETS_LIST_SET_PAGINATION');
const setElements = createAction('TICKETS_LIST_SET_ELEMENTS');
const loadList = createAction(
  'TICKETS_LIST_LOAD_LIST',
  (params) => dispatch => {
    dispatch(releaseCollection('Ticket', 'list'));
    repository('Ticket').search(params).then(response => {
      const res = response.getData();
      const ids = res.data.map(item=>item.id);
      dispatch(setCollection('Ticket', 'list', response.getData().data));
      dispatch(setPagination(response.getData().meta.pagination));
      dispatch(setElements(ids));
    });
  }
);

// Public --------------------------------------------------------------------------------------------------------------

export const unload = createAction('TICKETS_LIST_UNLOAD');
export const applyListParams = createAction(
  'TICKETS_LIST_APPLY_LIST_PARAMS',
  (overwrite) => (dispatch, getState) => {
    const current = listParamsSelector(getState()).toJS();
    const params = { ...current, ...overwrite };

    // reset pagination when switching to another filter
    if (overwrite.filter) {
      delete params.page;
    }

    dispatch(setListParams(params));

    // reload if filter param is set i.e. navigation menu item is selected
    if (params.filter) {
      dispatch(loadList(params));
    }
  }
);

// Public (control bar) ------------------------------------------------------------------------------------------------

export const setOrderBy = createAction(
  'TICKETS_LIST_SET_ORDER_BY',
    orderBy => dispatch => dispatch(applyListParams({ order_by: orderBy }))
);
export const setOrderDir = createAction(
  'TICKETS_LIST_SET_ORDER_DIR',
    orderDir => dispatch => dispatch(applyListParams({ order_dir: orderDir }))
);
export const toggleTableFieldVisibility = createAction('TICKETS_LIST_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('TICKETS_LIST_TOGGLE_CARD_FIELD_VISIBILITY');
export const setViewMode = createAction('TICKETS_LIST_SET_VIEW_MODE');
