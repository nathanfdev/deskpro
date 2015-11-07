import React, { PropTypes } from 'react';

import { Header } from './Header';

export class TableView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <table cellSpacing="0" className="condensed-task-list">
          <Header />
        </table>
      </div>
    );
  }
}
