import React, { PropTypes } from 'react';
import { ListGroup } from './ListGroup';
import { TaskCardContainer } from '../TaskCardContainer';
import { TaskCard } from './TaskCard/TaskCard';

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
