import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from './TaskCard/TaskCardPreview';
import { CustomCardDragLayer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

export class KanbanView extends React.Component {

  static propTypes = {
    taskGroups:    PropTypes.object,
    onChangeGroup: PropTypes.func
  };

  render() {
    const { onChangeGroup, taskGroups } = this.props;

    return (
      <div className="kanban kanban-columns">
        {taskGroups.map((taskGroup, index) =>
          <ListGroup
            key={index}
            group={taskGroup}
            onChangeGroup={onChangeGroup}
            />
        )}

        <CustomCardDragLayer>
          <TaskCardPreviewContainer>
            <TaskCardPreview />
          </TaskCardPreviewContainer>
        </CustomCardDragLayer>
      </div>
    );
  }
}
