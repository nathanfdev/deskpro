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
