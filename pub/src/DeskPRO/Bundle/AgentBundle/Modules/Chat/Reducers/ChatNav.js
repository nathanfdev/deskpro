import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/chatNavActions';

export default class ChatNav extends Reducer {
  getInitialState() {
    return {
      lists: {
        my: {
          total: 0,
          groupBy: 'date_period',
          isGroupingControlVisible: false,
          items: [/* {count, group} */]
        },
        all: {
          total: 0,
          groupBy: 'agent',
          isGroupingControlVisible: false,
          items: [/* {count, group} */]
        }
      },

      agentNames: {/* id: name */},
      departmentNames: {/* id: name */}
    };
  }

  registerHandlers() {
    this
      .r(actions.loadCounts, this.countsLoaded)
      .r(actions.loadAgentName, this.agentNameLoaded)
      .r(actions.loadDepartmentName, this.departmentNameLoaded)
      .r(actions.toggleListGroupingVisibility, this.listGroupingVisibilityChanged)
      .r(actions.changeListGrouping, this.listGroupingChanged)
    ;
  }

  countsLoaded(prev, {payload}) {
    const {list, counts} = payload;

    let next = {...prev};
    next.lists[list].total = counts.count;
    next.lists[list].items = counts.nested.counts;

    return next;
  }

  agentNameLoaded(prev, {payload}) {
    let agentNames = prev.agentNames;
    agentNames = {...agentNames, [payload.id]: payload.name};

    return {...prev, agentNames};
  }

  departmentNameLoaded(prev, {payload}) {
    let departmentNames = prev.departmentNames;
    departmentNames = {...departmentNames, [payload.id]: payload.name};

    return {...prev, departmentNames};
  }

  listGroupingVisibilityChanged(prev, {payload}) {
    let next = {...prev};
    next.lists[payload].isGroupingControlVisible = !next.lists[payload].isGroupingControlVisible;

    return next;
  }

  listGroupingChanged(prev, {payload}) {
    const {list, groupBy} = payload;
    
    let next = {...prev};
    next.lists[list].groupBy = groupBy;

    return next;
  }
}
