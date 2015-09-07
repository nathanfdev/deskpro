import React from 'react';

export class TableHeader extends React.Component {

  render() {
    const {sortTable} = this.props;
    return (
      <thead>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Agent</th>
        <th>Labels</th>
        <th>Chat</th>
        <th></th>
        <th></th>
      </tr>
      </thead>
    );
  }
}