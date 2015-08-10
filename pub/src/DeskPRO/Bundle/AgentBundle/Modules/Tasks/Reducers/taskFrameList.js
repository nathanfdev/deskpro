import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TaskFrameList extends Reducer {
  getInitialState() {
    return {
      taskFrameList: null,
      taskFrameSource: null,
      taskFrameProjects: null
    };
  }
  
  tasksLoaded(state, action) {
    let projects = {};
    let links = {};

    if (typeof action.payload.projects !== 'undefined' && typeof action.payload.projects.data !== 'undefined') {
      projects = action.payload.projects.data;
    }

    if (typeof action.payload.linked_items !== 'undefined') {
      links = action.payload.linked_items;
    }

    return {
      ...state,
      taskFrameList: action.payload.data,
      taskFrameSource: action.payload.source,
      taskFrameProjects: projects,
      taskFrameLinks: links
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadTaskList, this.tasksLoaded)
  }
}
