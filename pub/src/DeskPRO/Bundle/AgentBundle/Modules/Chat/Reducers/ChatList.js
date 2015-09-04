import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/chatListActions';
import * as constants from '../../../../Constants/Constants'

export default class ChatList extends Reducer {
    getInitialState() {
        return {

            // list sorting options
            sort: 'date_created',
            order: constants.ORDER_DESC,

            // view mode (table or list)
            view: constants.VIEW_MODE_TABLE,

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
        return {...prev, order: prev.order === constants.ORDER_ASC ? constants.ORDER_DESC : constants.ORDER_ASC}
    }

    viewChanged(prev) {
        return {
            ...prev,
            view: prev.view === constants.VIEW_MODE_LIST ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_LIST
        }
    }
}
