import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/crmNavActions';
import * as AppActions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/ActionTypes";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export default class CrmNav extends Reducer {
  getInitialState() {
    return {
      viewMode: constants.VIEW_MODE_TABLE,
      viewModeOptions: [
        {field: constants.VIEW_MODE_TABLE, label: 'Table view', icon: 'fa-table'}, {
          field: constants.VIEW_MODE_LIST,
          label: 'List view',
          icon: 'fa-list'
        }
      ],
      elements: [],
      // Display Fields in Table/List view switcher
      tableViewFields: [
        {name: 'id', label: 'ID', className: 'id-col', status: constants.FIELD_SHOWN, priority: 3},
      ],
      listViewFields: [/* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} */],

      sort: 'date_created', /* Order By ... */
      sortName: 'Date', /* Label for Order By... */
      order: constants.ORDER_DESC, /* Asc, Desc */
      sortOptions: [/* @ToDo actualize field properties */
        {field: 'date_created', label: 'Created'},
        {field: 'name', label: 'Name'},
        {field: 'date_created', label: 'Last login'},
        {field: 'date_created', label: 'Organization'}
      ],
      labels: {
        person: [/* string */],
        organization: [/* string */]
      },
      users: {
        total: 0,
        groups: [/* {count, group} */]
      },
      organizations: {
        total: 0
      },
      agents: {
        total: 0,
        teams: [/* {count, group} */]
      },
      groupNames: {/* id: name */},
      teamNames: {/* id: name */}
    };
  }

  registerHandlers() {
    this
      .r(AppActions.TOGGLE_VIEW_MODE, this.viewModeChanged)
      .r(AppActions.TOGGLE_ORDER, this.orderChanged)
      .r(actions.loadUsersTotalCount, this.usersTotalCountLoaded)
      .r(actions.loadGroupsCounts, this.groupsCountsLoaded)
      .r(actions.loadOrganizationsTotalCount, this.organizationsTotalCountLoaded)
      .r(actions.loadAgentsTotalCount, this.agentsTotalCountLoaded)
      .r(actions.loadTeamsCounts, this.teamsCountsLoaded)
      .r(actions.loadPersonLabels, this.personLabelsLoaded)
      .r(actions.loadOrganizationLabels, this.organizationLabelsLoaded)
      .r(actions.loadGroups, this.groupsLoaded)
      .r(actions.loadTeams, this.teamsLoaded)
    ;
  }

  usersTotalCountLoaded(prev, {payload}) {
    const next       = {...prev};
    next.users.total = payload;

    return next;
  }

  groupsCountsLoaded(prev, {payload}) {
    const next        = {...prev};
    next.users.groups = payload;

    return next;
  }

  organizationsTotalCountLoaded(prev, {payload}) {
    const next               = {...prev};
    next.organizations.total = payload;

    return next;
  }

  agentsTotalCountLoaded(prev, {payload}) {
    const next        = {...prev};
    next.agents.total = payload;

    return next;
  }

  teamsCountsLoaded(prev, {payload}) {
    const next        = {...prev};
    next.agents.teams = payload;

    return next;
  }

  personLabelsLoaded(prev, {payload}) {
    const next         = {...prev};
    next.labels.person = payload;

    return next;
  }

  organizationLabelsLoaded(prev, {payload}) {
    const next               = {...prev};
    next.labels.organization = payload;

    return next;
  }

  groupsLoaded(prev, {payload}) {
    const next      = {...prev};
    next.groupNames = {};
    payload.forEach(group => next.groupNames[group.id] = group.title);

    return next;
  }

  teamsLoaded(prev, {payload}) {
    const next     = {...prev};
    next.teamNames = {};
    payload.forEach(team => next.teamNames[team.id] = team.name);

    return next;
  }

  viewModeChanged(prev) {
    const next    = {...prev};
    next.viewMode = prev.viewMode === constants.VIEW_MODE_LIST ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_LIST;
    return next;
  }


  orderChanged(prev) {
    const next = {...prev};
    next.order = prev.order === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC;
    return next;
  }

}
