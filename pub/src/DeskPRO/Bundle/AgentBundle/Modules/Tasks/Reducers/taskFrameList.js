import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TaskFrameList extends Reducer {
  getInitialState() {
    return {
      taskFrameList: null,
      taskFrameSource: null,
      taskFrameProjects: null,
      taskFrameLinks: null,
      taskFrameAgents: null,
      taskFrameTeams: null,
      taskFrameDepartments: null
    };
  }
  
  tasksLoaded(state, action) {
    let projects = [];
    let links = [];
    let agents = [];
    let teams = [];
    let departments = [];
    
    if (typeof action.payload.projects !== 'undefined' && typeof action.payload.projects.data !== 'undefined') {
      projects = action.payload.projects.data;
    }

    if (typeof action.payload.linked_items !== 'undefined') {
      links = action.payload['linked_items'];
    }

    if (typeof action.payload.people !== 'undefined') {
      agents = action.payload['people'];
    }

    if (typeof action.payload.teams !== 'undefined') {
      teams = action.payload['teams'];
    }

    if (typeof action.payload.departments !== 'undefined') {
      departments = action.payload['departments'];
    }

    return {
      ...state,
      taskFrameList: action.payload.data,
      taskFrameSource: action.payload.source,
      taskFrameProjects: projects,
      taskFrameLinks: links,
      taskFrameAgents: agents,
      taskFrameTeams: teams,
      taskFrameDepartments: departments
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadTaskList, this.tasksLoaded)
  }
}
