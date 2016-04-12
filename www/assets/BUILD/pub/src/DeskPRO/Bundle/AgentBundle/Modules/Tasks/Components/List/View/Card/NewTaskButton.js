import React from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TaskCardNew } from './TaskCard/TaskCardNew';

export class NewTaskButton extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      newTaskExpanded: false
    };
  }

  onOpenNewTaskForm = event => {
    event.preventDefault();
    this.setState({
      newTaskExpanded: true
    });
  };

  onCloseNewTaskForm = () => {
    this.setState({
      newTaskExpanded: false
    });
  };

  renderAddTaskButton() {
    return (
      <div className="card-add">
        [ <a href="#" onClick={this.onOpenNewTaskForm} ref="button">Add task</a> ]
      </div>
    );
  }

  renderNewTaskForm() {
    return (
      <ClickOut onClickOut={this.onCloseNewTaskForm} ignoreNodes={[this.refs.button, '.popup']}>
        {this.state.newTaskExpanded
          ? <TaskCardNew ref="card" onClose={this.onCloseNewTaskForm} />
          : null
        }
      </ClickOut>
    );
  }

  render() {
    return this.state.newTaskExpanded ? this.renderNewTaskForm() : this.renderAddTaskButton();
  }
}
