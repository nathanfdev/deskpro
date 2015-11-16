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
        {taskGroups.map((taskGroup, index) =>
          <ListGroup title={taskGroup.title} key={index}>
            {taskGroup.elements.map(task =>
              <TaskCardContainer task={task} key={task.get('id')}>
                <TaskCard />
              </TaskCardContainer>
            )}
          </ListGroup>
        )}
      </div>
    );
  }
}
