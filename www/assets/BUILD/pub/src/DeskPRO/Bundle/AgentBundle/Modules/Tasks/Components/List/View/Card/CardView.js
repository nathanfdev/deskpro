import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from './TaskCard/TaskCardPreview';
import { CustomCardDragLayer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { NewTaskButton } from './NewTaskButton';

export class CardView extends React.Component {

  static propTypes = {
    taskGroups:    PropTypes.object,
    onChangeGroup: PropTypes.func
  };

  render() {
    const { taskGroups, onChangeGroup } = this.props;

    return (
      <div>
        {taskGroups.map((taskGroup, index) =>
          <ListGroup
            key={index}
            group={taskGroup}
            onChangeGroup={onChangeGroup}
            />
        )}

        <NewTaskButton />

        <CustomCardDragLayer>
          <TaskCardPreviewContainer>
            <TaskCardPreview />
          </TaskCardPreviewContainer>
        </CustomCardDragLayer>
      </div>
    );
  }
}
