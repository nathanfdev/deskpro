import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCardContainer } from '../TaskCardContainer';
import { TaskDragCard } from './TaskCard/TaskDragCard';

export class CardView extends React.Component {

  static propTypes = {
    taskGroups: PropTypes.array
  };

  render() {
    const { taskGroups = [] } = this.props;

    return (
      <div>
        {taskGroups
          .filter(taskGroup => taskGroup.elements.length)
          .map((taskGroup, index) =>

          <ListGroup title={taskGroup.title}
                     key={index}
                     param={taskGroup.param}
                     value={taskGroup.value}>

            {taskGroup.elements.map(task =>
              <TaskCardContainer task={task} key={task.get('id')}>
                <TaskDragCard />
              </TaskCardContainer>
            )}
          </ListGroup>
        )}
      </div>
    );
  }
}
