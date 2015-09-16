import { createAction } from 'Ampliflux';
import { loadPeopleNames } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Actions/PeopleNamesActions';
import { loadDepartmentsNames } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Actions/DepartmentsNamesActions';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';

export const loadCounts = createAction(
  'CHAT_LOAD_CONVERSATIONS_COUNTS',
  (list, groupBy) =>
    (dispatch) => Chat.loadCounts(groupBy, (list === 'my' ? 'me' : null)).then(promise => {
      if (groupBy === 'department') {
        dispatch(loadDepartmentsNames('chatNav', promise.getData().data.nested.map(count => count.group)));
      } else if (groupBy === 'agent') {
        dispatch(loadPeopleNames('chatNav', promise.getData().data.nested.map(count => count.group)));
      }

      return {
        list,
        counts: promise.getData().data
      };
    })
);

export const toggleListGroupingVisibility = createAction(
  'CHAT_TOGGLE_LIST_GROUPING_VISIBILITY',
  list => list
);

export const changeListGrouping = createAction(
  'CHAT_CHANGE_LIST_GROUPING',
  (list, groupBy) => dispatch => {
    dispatch(loadCounts(list, groupBy));
    dispatch(toggleListGroupingVisibility(list));

    return {list, groupBy};
  }
);
