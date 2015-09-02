import ActionTypes from "../Actions/ActionTypes";
import { Reducer } from "Ampliflux/reducers";

export default class control_bar extends Reducer {
    getInitialState() {
        return {
            viewMode: 'table'
        };
    }

    switchViewMode(prev) {
        const next = {...prev};
        next.viewMode = prev.viewMode === 'list' ? 'table' : 'list';
        return next;
    }

    registerHandlers() {
        this
            .r(ActionTypes.SWITCH_VIEW_MODE, this.switchViewMode)
    }
}