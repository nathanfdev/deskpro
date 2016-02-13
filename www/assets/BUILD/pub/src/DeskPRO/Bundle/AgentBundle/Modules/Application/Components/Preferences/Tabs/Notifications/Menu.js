import React from 'react';

export class Menu extends React.Component {
  render() {
    return (
      <div>
        <a href="#">Notifications</a>
        <ul className="stat-types-list">
          <li><a href="#">Inbox</a></li>
          <li><a href="#">Everything Else</a></li>
        </ul>
      </div>
    );
  }
}
