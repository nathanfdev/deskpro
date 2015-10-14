import { createReducer } from 'Ampliflux';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {

  viewModeOptions: [
    {field: constants.VIEW_MODE_TABLE, label: 'Table view', icon: 'fa-table', current: true},
    {field: constants.VIEW_MODE_CARD, label: 'List view', icon: 'fa-list', current: false}
  ],

  // Display Fields in Table/List view switcher
  tableViewFields: [/* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} */],
  listViewFields:  [/* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} */],

  // list sorting options
  order: constants.ORDER_DESC,
  sortOptions: [
    {field: 'date_created', label: 'Date', icon: 'fa-calendar-o', current: true},
    {field: 'agent', label: 'Agent', icon: 'fa-calendar-o', current: false},
    {field: 'department', label: 'Department', icon: 'fa-calendar-o', current: false}
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

  [actions.changeSort]: (state, payload) => {
    let sortOptions = [];
    state.get('sortOptions').toJS().forEach(obj=> {
      const nextObj   = {...obj};
      nextObj.current = obj.field === payload;
      sortOptions.push(nextObj);
    });
    return state.set('sortOptions', Immutable.fromJS(sortOptions))
  },

  [actions.toggleViewMode]: (state, payload) => {
    let viewModeOptions = [];
    state.get('viewModeOptions').toJS().forEach(obj=> {
      const nextObj   = {...obj};
      nextObj.current = obj.field === payload;
      viewModeOptions.push(nextObj);
    });
    return state.set('viewModeOptions', Immutable.fromJS(viewModeOptions))
  },

  [actions.toggleOrder]: setFullPayload('order')

});
