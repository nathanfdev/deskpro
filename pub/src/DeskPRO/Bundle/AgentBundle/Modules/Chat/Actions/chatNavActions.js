import { createAction } from 'Ampliflux';
import { loadCounts as loadChatCounts } from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import { loadDepartments, releaseDepartmentsRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'chatNav';

export const loadCounts = createAction(
  'CHAT_NAV_LOAD_CONVERSATIONS_COUNTS',
  (list, groupBy) =>
    (dispatch) => loadChatCounts(groupBy, (list === 'my' ? 'me' : null)).then(promise => {
      if (groupBy === 'department') {
        dispatch(loadDepartments(recordStoresId, promise.getData().data.nested.map(count => count.group)));
      }

      return {
        list,
        counts: promise.getData().data
      };
    })
);

export const toggleListGroupingVisibility = createAction(
  'CHAT_NAV_TOGGLE_LIST_GROUPING_VISIBILITY'
);

export const changeListGrouping = createAction(
  'CHAT_NAV_CHANGE_LIST_GROUPING',
  (list, groupBy) => dispatch => {
    dispatch(loadCounts(list, groupBy));
    dispatch(toggleListGroupingVisibility(list));

    return {list, groupBy};
  }
);

export const unmount = createAction(
  'CHAT_NAV_UNMOUNT',
  () => dispatch => {
    dispatch(releaseDepartmentsRequest(recordStoresId));
  }
);
