import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import { Calendar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/Calendar';

export class DueDate extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onOpenCalendar = () => {
    this.refs.calendar.open();
  };

  render() {
    const { value, onChange } = this.props;
    const isOverdue = value && moment(value).isBefore();

    return (
      <div className="dpwd--card-line-item" onClick={this.onOpenCalendar}>
        <span className={classNames({'overdue': isOverdue})} onClick={this.onOpenCalendar}>
          <i className="fa fa-calendar-o"/> Due:
          <Calendar ref="calendar"
                    value={moment(value).format('MM/DD/YYYY hh:mm')}
                    onChange={onChange} />
        </span>

      </div>
    );
  }
}
