import ActionTypes from "../Actions/ActionTypes";
import { Reducer } from "Ampliflux/reducers";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export default class control_bar extends Reducer {
    getInitialState() {
        return {
            viewMode: 'table'
        };
    }

    switchViewMode(prev) {
        const next = {...prev};
        next.viewMode = prev.viewMode === constants.VIEW_MODE_LIST ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_LIST;
        return next;
    }

    registerHandlers() {
        this
            .r(ActionTypes.SWITCH_VIEW_MODE, this.switchViewMode)
    }
}