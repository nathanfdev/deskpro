import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';

export class Due extends React.Component {

  static propTypes = {
    date: PropTypes.string
  };

  render() {
    const { date } = this.props;
    const dateFormatted = date && moment(date).format('hh:mm a');
    const isOverdue = date && moment(date).isBefore();

    return (
      <span className={classNames('dpwd--card-line-item', {'overdue': isOverdue})}>
        <i className="fa fa-calendar-o"/> Due: {dateFormatted || 'N/A'}
        <input type="text" name="due-date" className="due-date-field" disabled="disabled"/>
      </span>
    );
  }
}
