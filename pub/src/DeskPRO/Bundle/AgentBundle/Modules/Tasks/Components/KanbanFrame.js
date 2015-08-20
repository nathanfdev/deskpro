import React from "react";
import TaskListCard from "../Components/TaskListCard";
import { connect } from 'redux/react';
import * as TaskActions from "../Actions/TaskListActions";

@connect(state => ({
  taskListList: state.taskListList
}))
export default class KanbanFrame extends React.Component {
  constructor(props) {
    super(props);
    // Temp project ID
    props.dispatch(TaskActions.loadLists(63));
  }

  render() {
    console.log(this.props);
    let lists = [];
    if (this.props.taskListList.taskList) {
      lists = this.props.taskListList.taskList;
    }

    let tasks = {};

    if (this.props.tasks && this.props.tasks.taskFrameList.length > 0) {
      this.props.tasks.taskFrameList.forEach((object) => {
        if (object.list) {
          if (typeof tasks['list_' + object.list] === 'undefined') {
            tasks['list_' + object.list] = [];
          }
          let task = object;
          task.project = this.props.projects[task.project];

          tasks['list_' + object.list].push(object);
        }
      });
    }

    return <div className="kanban-columns">
      {lists ? lists.map((taskList) => {
        return <div className="list" key={taskList.id}>
          <h1 className="kanban-list-header">{taskList.title}</h1>
          {
            tasks['list_' + taskList.id] ? tasks['list_' + taskList.id].map((task) => {
              return <TaskListCard task={task} key={task.id} departments={this.props.departments}
                                   agents={this.props.agents} teams={this.props.teams} />
            }) : ''
          }
        </div>;
      }) : ''}
    </div>;
  }
}