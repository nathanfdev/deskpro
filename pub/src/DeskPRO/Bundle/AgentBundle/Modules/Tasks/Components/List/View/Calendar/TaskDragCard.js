import React, { PropTypes } from 'react';
import { DragSource } from 'react-dnd';
import { getEmptyImage } from 'react-dnd-html5-backend';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { cardSourceSpec, cardSourceCollect } from '../TaskCardContainer';
import { CalendarItemButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Calendar/index';

@DragSource(constants.TYPE_TASK, cardSourceSpec, cardSourceCollect)
export class TaskDragCard extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired,
    connectDragSource: PropTypes.func.isRequired,
    onOpenCard: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.connectDragPreview(getEmptyImage(), {
      captureDraggingState: true
    });
  }

  render() {
    const { task, onOpenCard, connectDragSource } = this.props;

    return connectDragSource(
      <span>
        <CalendarItemButton title={task.get('title')} onOpenCard={onOpenCard} />
      </span>
    );
  }
}
