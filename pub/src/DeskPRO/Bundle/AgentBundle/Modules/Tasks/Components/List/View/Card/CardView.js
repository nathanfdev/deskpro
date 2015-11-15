import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard/TaskCard';

export class CardView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.object
  };

  render() {
    const { taskGroups = [] } = this.props;

    return (
      <div>
        {taskGroups.map((tasks, groupTitle) =>
          <ListGroup title={groupTitle}>
            {tasks.map((task, index) => <TaskCard task={task} key={index} />)}
          </ListGroup>
        )}
      </div>
    );
  }
}
