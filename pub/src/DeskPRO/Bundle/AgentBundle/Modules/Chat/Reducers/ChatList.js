import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/chatListActions';

export default class ChatList extends Reducer {
  getInitialState() {
    return {

      // list sorting options
      sort: 'date_created',
      order: 'desc',

      // view mode (table or list)
      view: 'table',

      // chats to display
      chats: []
    };
  }

  registerHandlers() {
    this
      .r(actions.load, this.listLoaded)
      .r(actions.sort, this.sortChanged)
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
    return {...prev, order: prev.order === 'asc' ? 'desc' : 'asc'}
  }

  viewChanged(prev) {
    return {...prev, view: prev.view === 'list' ? 'table' : 'list'}
  }
}
