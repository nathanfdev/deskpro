import { createAction } from 'Ampliflux/actions';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as Departments from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';

export const loadCounts = createAction(
    'CHAT_LOAD_CONVERSATIONS_COUNTS',
    (trigger, list, groupBy) => Chat.loadCounts(groupBy, (list === 'my' ? 'me' : null)).then(promise => {
      trigger({
        list,
        counts: promise.getData().data
      });

      if (groupBy === 'department') {
        const nested = promise.getData().data.nested.counts;
        for (let i = 0; i < nested.length; i++) {
          trigger(loadDepartmentName(nested[i].group));
        }
      }

      if (groupBy === 'agent') {
        const nested = promise.getData().data.nested.counts;
        for (let i = 0; i < nested.length; i++) {
          trigger(loadAgentName(nested[i].group));
        }
      }
    })
);

export const loadAgentName = createAction(
    'CHAT_LOAD_AGENT_NAME',
    (trigger, id) => People.loadPerson(id).then(promise => {
      trigger({id, name: promise.getData().data.name});
    })
);

export const loadDepartmentName = createAction(
    'CHAT_LOAD_DEPARTMENT_NAME',
    (trigger, id) => Departments.loadDepartment(id).then(promise => {
      trigger({id, name: promise.getData().data.title});
    })
);

export const toggleListGroupingVisibility = createAction(
    'CHAT_TOGGLE_LIST_GROUPING_VISIBILITY',
    (trigger, list) => trigger(list)
);

export const changeListGrouping = createAction(
    'CHAT_CHANGE_LIST_GROUPING',
    (trigger, list, groupBy) => {
      trigger({list, groupBy});
      trigger(loadCounts(list, groupBy));
      trigger(toggleListGroupingVisibility(list));
    }
);