import React, { PropTypes } from 'react';

export class Header extends React.Component {

  render() {
    return (
      <thead>
        <tr>
          <th>Title</th>
          <th className="clickable-column">
            Project
            <span>
              <i className="fa fa-caret-down"/>
              <i className="fa fa-caret-up"/>
            </span>
          </th>
          <th className="clickable-column">
            Due
            <span>
              <i className="fa fa-caret-down"/>
              <i className="fa fa-caret-up"/>
            </span>
          </th>
          <th className="clickable-column">
            Assignee
            <span>
              <i className="fa fa-caret-down"/>
              <i className="fa fa-caret-up"/>
            </span>
          </th>
        </tr>
      </thead>
    );
  }
}
