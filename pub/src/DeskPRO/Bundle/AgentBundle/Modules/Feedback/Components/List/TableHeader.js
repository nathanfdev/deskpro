import React from 'react';

export class TableHeader extends React.Component {

  render() {
    const {sortTable} = this.props;
    return (
      <thead>
      <tr>
        <th className="id-col sortable" onClick={sortTable.bind(this, 'id')}>
          ID
        </th>
        <th className="sortable" onClick={sortTable.bind(this, 'num_ratings')}>
          Votes
        </th>
        <th className="subject-col sortable" onClick={sortTable.bind(this, 'title')}>
          Title
        </th>
        <th className="sortable" onClick={sortTable.bind(this, 'status')}>
          Status
        </th>
        <th className="sortable" onClick={sortTable.bind(this, 'category')}>
          Type
        </th>
        <th>Labels</th>
        <th className="user-col sortable" onClick={sortTable.bind(this, 'author_name')}>
          Submitter
        </th>
      </tr>
      </thead>
    );
  }
}