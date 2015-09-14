import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/chatListActions';
import * as AppActions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/ActionTypes";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export default class ChatList extends Reducer {

  getInitialState() {
    return {
      query: {agent: 'me'},

      // Display Fields in Table/List view switcher
      tableViewFields: [/* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} */],
      listViewFields: [/* {name: 'id', label: 'ID', status: constants.FIELD_SHOWN, priority: 1} */],

      // list sorting options
      sort: 'date_created',
      sortName: 'Date',
      sortOptions: [
        {field: 'date_created', label: 'Date'},
        {field: 'total_rating', label: 'Agent'},
        {field: 'num_ratings', label: 'Department'}
      ],
      order: constants.ORDER_DESC,

      // view mode (table or list)
      viewMode: constants.VIEW_MODE_TABLE,

      // chats to display
      elements: []
    };
  }

  registerHandlers() {
    this
      .r(actions.load, this.listLoaded)
      .r(actions.toggleSort, this.sortChanged)
      .r(AppActions.TOGGLE_ORDER, this.orderChanged)
      .r(AppActions.TOGGLE_VIEW_MODE, this.viewChanged)
    ;
  }

  listLoaded(prev, {payload}) {
    return {...prev, elements: payload};
  }

  sortChanged(prev, {payload}) {
    return {...prev, sort: payload};
  }

  orderChanged(prev) {
    const next = {...prev};
    next.order = prev.order === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC;
    return next;
  }

  viewChanged(prev) {
    return {
      ...prev,
      viewMode: prev.viewMode === constants.VIEW_MODE_LIST ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_LIST
    }
  }
}
