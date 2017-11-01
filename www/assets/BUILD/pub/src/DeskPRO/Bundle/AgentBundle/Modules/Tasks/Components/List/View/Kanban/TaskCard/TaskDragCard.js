import PropTypes from 'prop-types';
import React from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import { getEmptyImage } from 'react-dnd-html5-backend';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { cardSourceSpec, cardSourceCollect, cardTargetSpec, targetCollect } from '../../../TaskCard/TaskCardEditContainer';
import { TaskCard } from './TaskCard';
import classNames from 'classnames';

@DragSource(constants.TYPE_TASK, cardSourceSpec, cardSourceCollect)
@DropTarget(constants.TYPE_TASK, cardTargetSpec, targetCollect)
export class TaskDragCard extends React.Component {

  static propTypes = {
    selected:           PropTypes.bool,
    onToggleSelected:   PropTypes.func,
    task:               PropTypes.object,
    currentSort:        PropTypes.string,
    connectDragSource:  PropTypes.func.isRequired,
    connectDragPreview: PropTypes.func.isRequired,
    connectDropTarget:  PropTypes.func.isRequired,
    isOver:             PropTypes.bool,
    isDragging:         PropTypes.bool
  };

  componentDidMount() {
    this.props.connectDragPreview(getEmptyImage(), {
      captureDraggingState: true
    });
  }

  render() {
    const { currentSort, isOver, isDragging } = this.props;
    const { connectDragSource, connectDropTarget } = this.props;

    let result = connectDragSource(
      <div>
        <TaskCard dragging={isDragging} {...this.props} />
        <div className={classNames('placeholder', { 'is-over': isOver })} />
      </div>
    );

    if (currentSort === 'list') {
      result = connectDropTarget(result);
    }

    return result;
  }
}
