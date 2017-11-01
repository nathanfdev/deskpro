import PropTypes from 'prop-types';
import React from 'react';
import { DragSource } from 'react-dnd';
import { getEmptyImage } from 'react-dnd-html5-backend';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { cardSourceSpec, cardSourceCollect } from '../../../TaskCard/TaskCardEditContainer';

@DragSource(constants.TYPE_TASK, cardSourceSpec, cardSourceCollect)
export class TaskDragCard extends React.Component {

  static propTypes = {
    task:               PropTypes.object.isRequired,
    connectDragSource:  PropTypes.func.isRequired,
    connectDragPreview: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.connectDragPreview(getEmptyImage(), { captureDraggingState: true });
  }

  render() {
    const { task, connectDragSource } = this.props;

    return connectDragSource(
      <span>
        {task.get('title')}
      </span>
    );
  }
}
