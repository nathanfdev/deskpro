import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';

const weekdays = [
  'Sunday',
  'Monday',
  'Tuesday',
  'Wednesday',
  'Thursday',
  'Friday',
  'Saturday'
];

export class Header extends React.Component {

  static propTypes = {
    date: PropTypes.object.isRequired
  };

  render() {
    const today = moment();
    const currentMonth = today.isSame(this.props.date, 'month') && today.isSame(this.props.date, 'year');

    return (
      <thead>
        <tr>
          {weekdays.map(weekday =>
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
