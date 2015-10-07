import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  departmentCount: 0,
  departmentList: []
};

export default createReducer(initialState, {
  [TaskListActions.loadDepartments]: (state, payload) => {
    const departmentCount = payload.meta ? payload.meta.total_count : 0;

    const withCount = state.set('departmentCount', departmentCount);
    return withCount.set('departmentList', payload.data);
  }
});
