import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/imListActions.js';

export default class IMList extends Reducer {

    getInitialState() {
        return {
            elements: []
        };
    }

    registerHandlers() {
        this.r(actions.loadAgents, this.listLoaded);
    }

    listLoaded(prev, {payload}) {
        return {...prev, elements: payload};
    }
}
