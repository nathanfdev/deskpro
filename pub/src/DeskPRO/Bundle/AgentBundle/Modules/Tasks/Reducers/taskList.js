import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TaskList extends Reducer {
  getInitialState() {
    return {
    	taskList: null,
      taskCount: 0,
      myTaskCount: 0,
      teamTaskCount: 0,
      deptTaskCount: 0,
      delegatedTaskCount: 0,
      unassignedTaskCount: 0
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadTasks, this.tasksLoaded)
    .r(TaskListActions.loadMyTasks, this.myTasksLoaded)
    .r(TaskListActions.loadTeamTasks, this.teamTasksLoaded)
    .r(TaskListActions.loadDepartmentTasks, this.departmentTasksLoaded)
    .r(TaskListActions.loadDelegatedTasks, this.delegatedTasksLoaded)
    .r(TaskListActions.loadUnassignedTasks, this.unassignedTasksLoaded)
  }
  
  tasksLoaded(state, action) {
    return {
        ...state,
        taskList: action.payload.data,
        taskCount: action.payload.meta.pagination.total
    };
  }
  
  myTasksLoaded(state, action) {
    return {
      ...state,
      myTaskCount: action.payload.meta.pagination.total
    };
  }
  
  teamTasksLoaded(state, action) {
    return {
      ...state,
      teamTaskCount: action.payload.meta.pagination.total
    };
  }
  
  departmentTasksLoaded(state, action) {
    return {
      ...state,
      deptTaskCount: action.payload.meta.pagination.total
    };
  }
  
  delegatedTasksLoaded(state, action) {
    return {
      ...state,
      delegatedTaskCount: action.payload.meta.pagination.total
    };
  }
  
  unassignedTasksLoaded(state, action) {
    return {
      ...state,
      unassignedTaskCount: action.payload.meta.pagination.total
    };
  }
}
