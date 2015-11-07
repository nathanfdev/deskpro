import React, { PropTypes } from 'react';

export class Month extends React.Component {

  render() {
    return (
      <div className="dpwd-calendar-controls-month">
        <span className="dpwd-calendar-controls-month-last">
          <i className="fa fa-caret-left" />
        </span>
        <span className="dpwd-calendar-controls-month-text">September</span>
        <span className="dpwd-calendar-controls-month-next">
          <i className="fa fa-caret-right" />
        </span>
      </div>
    );
  }
}
