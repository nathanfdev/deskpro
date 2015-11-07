import React, { PropTypes } from 'react';

export class TableView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>TableView</div>
    );
  }
}
