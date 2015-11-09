import React, { PropTypes } from 'react';
import { Header } from './Header/Header';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard/TaskCard';

export class TableView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
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
    return (
      <div>
        <table cellSpacing="0" className="condensed-task-list">
          <Header currentOrder={this.state.currentOrder}
                  currentDirection={this.state.currentDirection}
                  onChange={this.onSort} />

          <ListGroup title="Overdue">
            {this.props.tasks.map((task, index) => <TaskCard task={task} key={index} />)}
          </ListGroup>
        </table>
      </div>
    );
  }
}
