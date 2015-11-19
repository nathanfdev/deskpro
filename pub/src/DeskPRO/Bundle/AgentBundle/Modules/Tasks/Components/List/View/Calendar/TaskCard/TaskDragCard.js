import React, { PropTypes } from 'react';
import { DragSource } from 'react-dnd';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { cardSourceSpec, cardSourceCollect } from '../../TaskCardContainer';

@DragSource(constants.TYPE_TASK, cardSourceSpec, cardSourceCollect)
export class TaskDragCard extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired,
    currentSort: PropTypes.string,
    connectDragSource: PropTypes.func.isRequired,
    isOver: PropTypes.bool,
    isDragging: PropTypes.bool,
    onOpenTaskCard: PropTypes.func.isRequired
  };

  render() {
    const { task, onOpenTaskCard, connectDragSource } = this.props;

    return connectDragSource(
      <span>
        <a href="#" onClick={onOpenTaskCard}>
          {task.get('title')}
        </a>
      </span>
    );
  }
}
