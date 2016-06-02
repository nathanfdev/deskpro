import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { listParamsSelector } from '../Selectors/list';
import { setCollection, releaseCollection } from '../../../../AppBundle/Modules/RecordsStore';

// Private -------------------------------------------------------------------------------------------------------------

const setListParams = createAction('TICKETS_LIST_SET_LIST_PARAMS');
const setPagination = createAction('TICKETS_LIST_SET_PAGINATION');
const setElements   = createAction('TICKETS_LIST_SET_ELEMENTS');

const loadList = createAction(
  'TICKETS_LIST_LOAD_LIST',
  (params) => dispatch => {
    dispatch(releaseCollection('Ticket', 'list'));
    repository('Ticket').search(params).then(response => {
      const res = response.getData();
      const ids = res.data.map(item => item.id);
      dispatch(setCollection('Ticket', 'list', response.getData().data));
      dispatch(setPagination(response.getData().meta.pagination));
      dispatch(setElements(ids));
    });
  }
);

// Public --------------------------------------------------------------------------------------------------------------
export const loadIndicator   = createAction('TICKET_LIST_LOAD_INDICATOR');
export const unload          = createAction('TICKETS_LIST_UNLOAD');
export const applyListParams = createAction(
  'TICKETS_LIST_APPLY_LIST_PARAMS',
  (overwrite) => (dispatch, getState) => {
    const current = listParamsSelector(getState()).toJS();

    // reset pagination and previous filter settings when switching to another filter
    if (overwrite.filter || overwrite.label || overwrite.star) {
      delete current.page;
      const stableProps = ['order_by', 'order_dir', 'date_created', 'labels', 'status'];
      Object.keys(current).forEach(key => {
        if (stableProps.indexOf(key) === -1) {
          delete current[key];
        }
      });
      if (overwrite.label) {
        delete current.labels;
      }
    }

    const params = { ...current, ...overwrite };
    dispatch(setListParams(params));

    // reload if filter param is set i.e. navigation menu item is selected
    if (params.filter || overwrite.label || overwrite.star) {
      dispatch(loadList(params));
    }
  }
);

// Public (control bar) ------------------------------------------------------------------------------------------------

export const setOrderBy                 = createAction(
  'TICKETS_LIST_SET_ORDER_BY',
  orderBy => dispatch => dispatch(applyListParams({ order_by: orderBy }))
);
export const toggleFieldVisibility = createAction('TICKETS_LIST_TOGGLE_FIELD_VISIBILITY');
export const changeFieldOrder      = createAction('TICKETS_LIST_CHANGE_FIELD_ORDER');
export const setViewMode           = createAction('TICKETS_LIST_SET_VIEW_MODE');
