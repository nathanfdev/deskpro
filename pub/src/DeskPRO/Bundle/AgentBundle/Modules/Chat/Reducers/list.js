import { createReducer } from 'Ampliflux';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatListActions';
import * as AppActions from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/ActionTypes';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {

  viewModeOptions: [
    {field: constants.VIEW_MODE_TABLE, label: 'Table view', icon: 'fa-table', current: true},
    {field: constants.VIEW_MODE_LIST, label: 'List view', icon: 'fa-list', current: false}
  ],

  // Display Fields in Table/List view switcher
  tableViewFields: [/* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} */],
  listViewFields:  [/* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} */],

  // list sorting options
  order: constants.ORDER_DESC,
  sort: 'date_created',
  sortName: 'Date',
  sortOptions: [
    {field: 'date_created', label: 'Date', current: true},
    {field: 'agent', label: 'Agent', current: false},
    {field: 'department', label: 'Department', current: false}
  ],

  // chats to display
  elements: [],

  // query parameters of the current list
  currentListParams: {}
};

export default createReducer(initialState, {

  [actions.load]:   async({success: setFullPayload('elements')}),
  [actions.reLoad]: async({success: setFullPayload('elements')}),

  [actions.updateCurrentListParams]: setFullPayload('currentListParams'),

  [actions.changeSort]: setFullPayload('sort'),

  [AppActions.TOGGLE_ORDER]: state =>
    state.set(
      'order',
      state.get('order') === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC
    ),
});
