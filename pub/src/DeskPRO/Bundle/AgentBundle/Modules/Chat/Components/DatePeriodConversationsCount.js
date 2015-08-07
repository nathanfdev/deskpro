import React from 'react';
import * as actions from '../Actions/chatConversationsNavFrameActions'

export default class DatePeriodConversationsCount extends React.Component {
  static labels = {
    today: 'Today',
    yesterday: 'Yesterday',
    this_week: 'This Week',
    this_month: 'This Month',
    last_month: 'Last Month',
    this_year: 'This Year',
    ever: 'Ever',
  };

  render() {
    console.log("rendering DatePeriodConversationsCount");

    const {count, period} = this.props;

    return (
      <li>
        <div className="list-counter-bucket">
          <a className="list-counter" href="#">{count}</a>
        </div>
        <a href="#" className="item">{this.getLabel(period)}</a>
      </li>
    );
  }

  getLabel(period) {
    return DatePeriodConversationsCount.labels[period];
  }
}
