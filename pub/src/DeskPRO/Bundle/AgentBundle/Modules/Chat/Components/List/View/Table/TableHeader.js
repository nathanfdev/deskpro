import React from 'react';

export class TableHeader extends React.Component {

  render() {
    return (
      <thead>
      <tr>
        <th className="id-col sortable">ID</th>
        <th className="user-col sortable">User</th>
        <th className="agent-col sortable">Agent</th>
        <th>Labels</th>
        <th>Chat</th>
        <th>Department</th>
        <th></th>
      </tr>
      </thead>
    );
  }
}