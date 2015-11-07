import React, { PropTypes } from 'react';

export class Year extends React.Component {

  render() {
    return (
      <div className="dpwd-calendar-controls-year">
        <span className="dpwd-calendar-controls-year-text">2015</span>
          <span className="dpwd-calendar-controls-year-dropdown">
            <i className="fa fa-caret-down" />
          </span>
      </div>
    );
  }
}
