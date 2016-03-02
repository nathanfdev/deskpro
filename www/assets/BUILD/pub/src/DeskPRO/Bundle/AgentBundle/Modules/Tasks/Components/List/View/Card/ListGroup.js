import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { TaskCardEditContainer, groupTargetSpec, targetCollect } from '../../TaskCard/TaskCardEditContainer';
import { CardGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import { NewTaskButton } from './NewTaskButton';
import classNames from 'classnames';
import { TaskDragCard } from './TaskCard/TaskDragCard';

@DropTarget(constants.TYPE_TASK, groupTargetSpec, targetCollect)
export class ListGroup extends React.Component {

  static propTypes = {
    group: PropTypes.object.isRequired,
    isOver: PropTypes.bool,
    connectDropTarget: PropTypes.func.isRequired,
    onUpdate: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      title: props.group.get('title'),
      elements: props.group.get('elements'),
      updateData: props.group.get('updateData'),
      isOver: props.isOver
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      title: props.group.get('title'),
      elements: props.group.get('elements'),
      updateData: props.group.get('updateData'),
      isOver: props.isOver
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.elements, state.elements) || this.state.isOver !== state.isOver;
  }

  componentWillUpdate(props, state) {
    this.props.onUpdate && this.props.onUpdate(state.elements);
  }

  onUpdate(key, task) {
    this.setState({
      elements: this.state.elements.set(key, task)
    });
  }

  render() {
    const { connectDropTarget } = this.props;
    const { isOver, elements, title, updateData } = this.state;

    return connectDropTarget(
      <div className={classNames({'list-group-hover': isOver})}>
        <CardGroupDivider title={title} />

        {elements.valueSeq().map((task, key) =>
          <TaskCardEditContainer task={task}
                                 updateData={updateData}
                                 onUpdate={this.onUpdate.bind(this, key)}>
            <TaskDragCard />
          </TaskCardEditContainer>
        )}
      </div>
    );
  }
}
