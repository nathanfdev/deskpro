import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskDragCard } from './TaskCard/TaskDragCard';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from './TaskCard/TaskCardPreview';
import { CustomCardDragLayer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class KanbanView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.array,
    onChangeGroup: PropTypes.func
  };

  render() {
    const { taskGroups = [], onChangeGroup } = this.props;

    return (
      <div className="kanban kanban-columns">
        {taskGroups.map((taskGroup, index) =>
          <ListGroup title={taskGroup.title}
                     key={index}
                     updateData={taskGroup.updateData}
                     onChangeGroup={onChangeGroup}>

            {taskGroup.elements.map(task =>
              <TaskCardEditContainer task={task} key={task.get('id')}>
                <TaskDragCard />
              </TaskCardEditContainer>
            )}
          </ListGroup>
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
