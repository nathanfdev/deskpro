import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/imListActions.js';

export default class IMList extends Reducer {

    getInitialState() {
        return {
            agents: [],
            teams: [],
            departments: []
        };
    }

    registerHandlers() {
        this.r(actions.loadAgents, this.listAgents);
        this.r(actions.loadTeams, this.listTeams);
        this.r(actions.loadDepartments, this.listDepartments);
    }

    listAgents(prev, {payload}) {
        return {...prev, agents: payload};
    }

    listTeams(prev, {payload}) {
        return {...prev, teams: payload};
    }

    listDepartments(prev, {payload}) {
        return {...prev, departments: payload};
    }
}
