import React, { PropTypes } from 'react';
import { Header } from './Header/Header';
import { ListGroup } from './ListGroup';
import { TaskCardContainer } from '../TaskCardContainer';
import { TaskCard } from './TaskCard/TaskCard';

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
      <div>
        <table cellSpacing="0" className="condensed-task-list">
          <Header currentOrder={this.state.currentOrder}
                  currentDirection={this.state.currentDirection}
                  onChange={this.onSort} />

          {taskGroups.map(taskGroup =>
            <ListGroup title={taskGroup.title}>
              {taskGroup.elements.map((task, index) =>
                <TaskCardContainer task={task} key={index}>
                  <TaskCard task={task} key={index} />
                </TaskCardContainer>
              )}
            </ListGroup>
          )}
        </table>
      </div>
    );
  }
}
