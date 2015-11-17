import React, { PropTypes } from 'react';
import { Header } from './Header';
import { ListGroup } from './ListGroup';
import { TaskCardContainer } from '../TaskCardContainer';
import { TaskCard } from './TaskCard/TaskCard';
import { Table } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class TableView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.array
  };

  constructor(props) {
    super(props);

    this.state = {
      currentOrder: 'project',
      currentDirection: 'desc'
    };
  }

  onSort = (order, direction) => {
    this.setState({
      currentOrder: order,
      currentDirection: direction
    });
  };

  render() {
    const { taskGroups = [] } = this.props;

    return (
      <Table>
          <Header currentOrder={this.state.currentOrder}
                  currentDirection={this.state.currentDirection}
                  onChange={this.onSort} />

          {taskGroups
            .filter(taskGroup => taskGroup.elements.length)
            .map((taskGroup, index) =>

            <ListGroup title={taskGroup.title} key={index}>
              {taskGroup.elements.map(task =>
                <TaskCardContainer task={task} key={task.get('id')}>
                  <TaskCard />
                </TaskCardContainer>
              )}
            </ListGroup>
          )}
      </Table>
    );
  }
}
