import React, { PropTypes } from 'react';
import { Header } from './Header/Header';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard';

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
            <TaskCard />
            <TaskCard />
            <TaskCard />
            <TaskCard />
            <TaskCard />
          </ListGroup>
        </table>
      </div>
    );
  }
}
