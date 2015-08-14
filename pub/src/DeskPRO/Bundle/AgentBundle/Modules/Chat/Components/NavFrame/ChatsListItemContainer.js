import React from 'react';
import { connect } from 'redux/react';
import { ChatsListItem } from './ChatsListItem';

@connect(state => ({
  labels: {
    agent: state.ChatNav.agentNames,
    department: state.ChatNav.departmentNames,
    date_period: {
      today:      'Today',
      yesterday:  'Yesterday',
      this_week:  'This Week',
      this_month: 'This Month',
      last_month: 'Last Month',
      this_year:  'This Year',
      ever:       'Ever',
    }
  }
}))
export class ChatsListItemContainer extends React.Component {
  render() {
    const {count, group, groupBy} = this.props;
    const label = this.props.labels[groupBy][group];

    return (
      <ChatsListItem count={count} label={label} />
    );
  }
}
