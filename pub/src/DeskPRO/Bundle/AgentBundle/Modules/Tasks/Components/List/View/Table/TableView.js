import React, { PropTypes } from 'react';
import { HeaderContainer } from './HeaderContainer';
import { TaskCardContainer } from '../TaskCardContainer';
import { TaskCard } from './TaskCard/TaskCard';
import { Table, TableGroup } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class TableView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.array
  };

  render() {
    const { taskGroups = [] } = this.props;

    return (
      <Table>
          <HeaderContainer />

          {taskGroups
            .filter(taskGroup => taskGroup.elements.length)
            .map((taskGroup, index) =>

            <TableGroup title={taskGroup.title} key={index}>
              {taskGroup.elements.map(task =>
                <TaskCardContainer task={task} key={task.get('id')}>
                  <TaskCard />
                </TaskCardContainer>
              )}
            </TableGroup>
          )}
      </Table>
    );
  }
}
