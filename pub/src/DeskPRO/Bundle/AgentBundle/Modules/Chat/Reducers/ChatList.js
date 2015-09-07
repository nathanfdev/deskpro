import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/chatListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export default class ChatList extends Reducer {
  getInitialState() {
    return {

      // list sorting options
      sort: {
        sort: 'date_created',
        name: 'Date',
        order: constants.ORDER_DESC
      },
      sortOptions: [
        {field: 'date_created', label: 'Date'},
        {field: 'total_rating', label: 'Agent'},
        {field: 'num_ratings', label: 'Department'}
      ],

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
      .r(actions.toggleOrder, this.orderChanged)
      .r(actions.toggleView, this.viewChanged)
    ;
  }

  listLoaded(prev, {payload}) {
    return {...prev, chats: payload};
  }

  sortChanged(prev, {payload}) {
    return {...prev, sort: payload};
  }

  orderChanged(prev) {
    const next = {...prev};
    next.sort.order = prev.sort.order === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC;
    return next;
  }

  viewChanged(prev) {
    return {
      ...prev,
      viewMode: prev.view === constants.VIEW_MODE_LIST ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_LIST
    }
  }
}
