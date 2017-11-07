import React from 'react';

export class FollowUpTime extends React.Component {
  render() {
    return (
      <div className="time">
        <ul>
          <li>15 minutes</li>
          <li>1 hour</li>
          <li>6 hours</li>
          <li>1 day</li>
          <li>3 days</li>
        </ul>
      </div>
    );
  }
}
