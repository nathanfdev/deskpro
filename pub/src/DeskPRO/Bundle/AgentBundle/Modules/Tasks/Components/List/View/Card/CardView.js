import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCardContainer } from '../../TaskCard/TaskCardContainer';
import { TaskDragCard } from './TaskCard/TaskDragCard';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from './TaskCard/TaskCardPreview';
import { CustomCardDragLayer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class CardView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.array,
    onChangeGroup: PropTypes.func
  };

  render() {
    const { taskGroups = [], onChangeGroup } = this.props;

    return (
      <div>
        {taskGroups
          .filter(taskGroup => taskGroup.elements.length)
          .map((taskGroup, index) =>

          <ListGroup title={taskGroup.title}
                     key={index}
                     updateData={taskGroup.updateData}
                     onChangeGroup={onChangeGroup}>

            {taskGroup.elements.map(task =>
              <TaskCardContainer task={task} key={task.get('id')}>
                <TaskDragCard />
              </TaskCardContainer>
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
