import React, { PropTypes } from 'react';
import { Header } from './Header/Header';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard/TaskCard';

export class TableView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <table cellSpacing="0" className="condensed-task-list">
          <Header currentOrder="project"
                  currentDirection="desc" />

          <ListGroup title="Overdue">
            {this.props.tasks.map((task, index) => <TaskCard task={task} key={index} />)}
          </ListGroup>
        </table>
      </div>
    );
  }
}
