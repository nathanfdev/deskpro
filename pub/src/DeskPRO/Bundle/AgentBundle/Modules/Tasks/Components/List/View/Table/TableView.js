import React, { PropTypes } from 'react';
import { HeaderContainer } from './HeaderContainer';
import { TaskCardEditContainer } from '../../TaskCard/TaskCardEditContainer';
import { TaskCard } from './TaskCard/TaskCard';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from '../Card/TaskCard/TaskCardPreview';
import { ListGroup } from './ListGroup';
import { Table, CustomCardDragLayer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

export class TableView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.array,
    onChangeGroup: PropTypes.func
  };

  render() {
    const { taskGroups = [], onChangeGroup } = this.props;

    return (
      <div>
        <Table>
            <HeaderContainer />

            {taskGroups
              .filter(taskGroup => taskGroup.elements.length)
              .map((taskGroup, index) =>

              <ListGroup title={taskGroup.title}
                         key={index}
                         updateData={taskGroup.updateData}
                         onChangeGroup={onChangeGroup}>

                {taskGroup.elements.map(task =>
                  <TaskCardEditContainer task={task}
                                         key={task.get('id')}
                                         updateData={taskGroup.updateData}>
                    <TaskCard />
                  </TaskCardEditContainer>
                )}
              </ListGroup>
            )}
        </Table>

        <CustomCardDragLayer>
          <TaskCardPreviewContainer>
            <TaskCardPreview />
          </TaskCardPreviewContainer>
        </CustomCardDragLayer>
      </div>
    );
  }
}
