import { createReducer } from 'Ampliflux';
import { async, setValue, mergeFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/crmNavActions';

const initialState = {
  async: {
    done: false
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
  }
};
export default createReducer(initialState, {
  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
/*
 import * as actions from '../Actions/crmNavActions';
 import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

 export default class CrmNav extends Reducer {
 getInitialState() {
 return {
 viewModeOptions: [
 {field: constants.VIEW_MODE_TABLE, label: 'Table view', icon: 'fa-table', current: true},
 {field: constants.VIEW_MODE_CARD, label: 'List view', icon: 'fa-list', current: false}
 ],
 order: constants.ORDER_DESC, /!* Asc, Desc *!/
 sortOptions: [/!* @ToDo actualize field properties *!/
 {field: 'date_created', label: 'Created', icon: 'fa-calendar-o', current: true},
 {field: 'name', label: 'Name', icon: 'fa-calendar-o', current: false}
 ],
 elements: [],
 // Display Fields in Table/List view switcher
 tableViewFields: [
 {name: 'id', label: 'ID', className: 'id-col', status: constants.FIELD_SHOWN, priority: 3},
 ],
 listViewFields: [/!* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} *!/],

 labels: {
 person: [/!* string *!/],
 organization: [/!* string *!/]
 },
 users: {
 total: 0,
 groups: [/!* {count, group} *!/]
 },
 organizations: {
 total: 0
 },
 agents: {
 total: 0,
 teams: [/!* {count, group} *!/]
 }
 };
 }

 registerHandlers() {
 this
 .r('APP_TOGGLE_VIEW_MODE', this.viewModeChanged)
 .r('APP_TOGGLE_ORDER', this.orderChanged)
 .r(actions.loadUsersTotalCount, this.usersTotalCountLoaded)
 .r(actions.loadGroupsCounts, this.groupsCountsLoaded)
 .r(actions.loadOrganizationsTotalCount, this.organizationsTotalCountLoaded)
 .r(actions.loadAgentsTotalCount, this.agentsTotalCountLoaded)
 .r(actions.loadTeamsCounts, this.teamsCountsLoaded)
 .r(actions.loadPersonLabels, this.personLabelsLoaded)
 .r(actions.loadOrganizationLabels, this.organizationLabelsLoaded)
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

 viewModeChanged(prev) {
 const next    = {...prev};
 next.viewMode = prev.viewMode === constants.VIEW_MODE_CARD ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_CARD;
 return next;
 }

 orderChanged(prev) {
 const next = {...prev};
 next.order = prev.order === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC;
 return next;
 }
 }
 */
