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
    if (this.refs.card.state.isChanged) return;

    this.setState({
      newTaskExpanded: false
    });
  };

  renderAddTaskButton() {
    return (
      <div className="card-add">
        [ <a href="#" onClick={this.onOpenNewTaskForm}>Add task</a> ]
      </div>
    );
  }

  renderNewTaskForm() {
    return (
      <ClickOut onClickOut={this.onCloseNewTaskForm}>
        {this.state.newTaskExpanded
          ? <TaskCardNew ref="card" />
          : null
        }
      </ClickOut>
    );
  }

  render() {
    return this.state.newTaskExpanded ? this.renderNewTaskForm() : this.renderAddTaskButton();
  }
}
