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
  },
  labels: {
    person: [/* string */],
    organization: [/* string */]
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

 */
