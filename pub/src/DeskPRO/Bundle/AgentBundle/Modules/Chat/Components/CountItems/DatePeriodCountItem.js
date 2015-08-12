import React from 'react';
import * as actions from '../../Actions/chatConversationsNavFrameActions'

export class DatePeriodCountItem extends React.Component {
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
    const {count, group} = this.props;

    return (
      <li>
        <div className="list-counter-bucket">
          <a className="list-counter active" href="#">{count}</a>
        </div>
        <a href="#" className="item">{this.getLabel(group)}</a>
      </li>
    );
  }

  getLabel(group) {
    return DatePeriodCountItem.labels[group];
  }
}
