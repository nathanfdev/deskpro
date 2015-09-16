import { createAction } from 'Ampliflux';
import { loadCounts } from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import { loadPeopleNames, releasePeopleNamesRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Actions/PeopleNamesActions';
import { loadDepartmentsNames, releaseDepartmentsNamesRequest }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Actions/DepartmentsNamesActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'chatNav';

export const loadCounts = createAction(
  'CHAT_NAV_LOAD_CONVERSATIONS_COUNTS',
  (list, groupBy) =>
    (dispatch) => loadCounts(groupBy, (list === 'my' ? 'me' : null)).then(promise => {
      if (groupBy === 'department') {
        dispatch(loadDepartmentsNames(recordStoresId, promise.getData().data.nested.map(count => count.group)));
      } else if (groupBy === 'agent') {
        dispatch(loadPeopleNames(recordStoresId, promise.getData().data.nested.map(count => count.group)));
      }

      return {
        list,
        counts: promise.getData().data
      };
    })
);

export const toggleListGroupingVisibility = createAction(
  'CHAT_NAV_TOGGLE_LIST_GROUPING_VISIBILITY',
  list => list
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
  () => (dispatch) => {
    dispatch(releasePeopleNamesRequest(recordStoresId));
    dispatch(releaseDepartmentsNamesRequest(recordStoresId));
  }
);
