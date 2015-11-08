import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';

export class CalendarHeader extends React.Component {

  static propTypes = {
    date: PropTypes.object.isRequired
  };

  render() {
    const today = moment();
    const currentMonth = today.isSame(this.props.date, 'month');

    return (
      <thead>
        <tr>
          {moment.weekdays().map(weekday =>
            <td key={weekday}
                className={classNames({
                  'dpwd-calendar-header-today': currentMonth && today.format('dddd') === weekday
                })}>

              {weekday}
            </td>
          )}
        </tr>
      </thead>
    );
  }
}
