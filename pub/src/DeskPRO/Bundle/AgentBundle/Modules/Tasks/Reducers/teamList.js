import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  teamCount: 0,
  teamList: []
};

export default createReducer(initialState, {
  [TaskListActions.loadTeams]: (state, payload) => {
    const teamCount = payload.meta ? payload.meta.total_count : 0;

    const withCount = state.set('teamCount', teamCount);
    return withCount.set('teamList', payload.data);
  }
});
