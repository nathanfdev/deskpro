import React, { PropTypes } from 'react';
import { DropTarget } from 'react-dnd';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { groupTargetSpec, targetCollect } from '../../TaskCard/TaskCardEditContainer';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { CardGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { TaskCardNew } from './TaskCard/TaskCardNew';
import { TaskCardNewContainer } from '../../TaskCard/TaskCardNewContainer';
import classNames from 'classnames';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)
export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.any,
    children: PropTypes.node,
    isOver: PropTypes.bool,
    connectDropTarget: PropTypes.func.isRequired
  };

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
        [ <a href="#" onClick={this.onOpenNewTaskForm}>Add task</a> ]
      </div>
    );
  }

  renderNewTaskForm() {
    return (
      <ClickOut onClickOut={this.onCloseNewTaskForm}>
        <TaskCardNewContainer {...this.props} onClose={this.onCloseNewTaskForm}>
          <TaskCardNew />
        </TaskCardNewContainer>
      </ClickOut>
    );
  }

  render() {
    const { title, children, isOver, connectDropTarget } = this.props;

    return connectDropTarget(
      <div className={classNames({'list-group-hover': isOver})}>
        <CardGroupDivider title={title} />

        {children}

        {this.state.newTaskExpanded ? this.renderNewTaskForm() : this.renderAddTaskButton()}
      </div>
    );
  }
}
