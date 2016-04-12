import React, { PropTypes } from 'react';
import { HeaderContainer } from './HeaderContainer';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from '../Card/TaskCard/TaskCardPreview';
import { ListGroup } from './ListGroup';
import { Table, CustomCardDragLayer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

export class TableView extends React.Component {

  static propTypes = {
    taskGroups:    PropTypes.object,
    onChangeGroup: PropTypes.func
  };

  render() {
    const { taskGroups, onChangeGroup } = this.props;

    return (
      <div>
        <Table>
          <HeaderContainer />

          {taskGroups.map((taskGroup, index) =>
            <ListGroup
              key={index}
              group={taskGroup}
              onChangeGroup={onChangeGroup}
              onUpdate={() => this.onUpdate(index)}
              />
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
