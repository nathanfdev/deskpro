import { createAction } from 'Ampliflux';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as Departments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';

export const loadAgentName = createAction(
  'CHAT_LOAD_AGENT_NAME',
  id => People.loadPerson(id).then(promise => {
    return {id, name: promise.getData().data.name};
  })
);

export const loadDepartmentName = createAction(
  'CHAT_LOAD_DEPARTMENT_NAME',
  id => Departments.loadDepartment(id).then(promise => {
    return {id, name: promise.getData().data.title};
  })
);

export const loadCounts = createAction(
  'CHAT_LOAD_CONVERSATIONS_COUNTS',
  (list, groupBy) =>
    (dispatch) => Chat.loadCounts(groupBy, (list === 'my' ? 'me' : null)).then(promise => {
      if (groupBy === 'department') {
        const nested = promise.getData().data.nested;
        for (let i = 0; i < nested.length; i++) {
          dispatch(loadDepartmentName(nested[i].group));
        }
      }

      if (groupBy === 'agent') {
        const nested = promise.getData().data.nested;
        for (let i = 0; i < nested.length; i++) {
          dispatch(loadAgentName(nested[i].group));
        }
      }

      const result = {
        list,
        counts: promise.getData().data
      };

      console.log('CHAT_LOAD_CONVERSATIONS_COUNTS: resolving promise to ', result);

      return result;
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