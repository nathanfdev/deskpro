import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard/TaskCard';

export class KanbanView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <ListGroup title="Overdue">
          {this.props.tasks.map((task, index) => <TaskCard task={task} key={index} />)}
        </ListGroup>
      </div>
    );
  }
}
