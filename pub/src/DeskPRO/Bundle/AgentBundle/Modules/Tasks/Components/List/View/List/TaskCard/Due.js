import React, { PropTypes } from 'react';

export class Due extends React.Component {

  static propTypes = {
    due: PropTypes.string
  };

  render() {
    return (
      <span className="overdue dpwd--card-line-item">
        <i className="fa fa-calendar-o"/> Due: {this.props.due}
        <input type="text" name="due-date" className="due-date-field" disabled="disabled"/>
      </span>
    );
  }
}
