import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard';
import { TaskCardContainer } from '../TaskCardContainer';

export class KanbanView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.array
  };

  render() {
    const { taskGroups = [] } = this.props;

    return (
      <div className="kanban kanban-columns">
        {taskGroups.map(taskGroup =>
          <ListGroup title={taskGroup.title}>
            {taskGroup.elements.map((task, index) =>
              <TaskCardContainer task={task} key={index}>
                <TaskCard />
              </TaskCardContainer>
            )}
          </ListGroup>
        )}
      </div>
    );
  }
}
