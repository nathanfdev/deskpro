import React, { PropTypes } from 'react';
import { Header } from './Header/Header';

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
        </table>
      </div>
    );
  }
}
