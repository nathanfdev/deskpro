import React from 'react';
import { connect } from 'redux/react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

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
export class ChatsListItem extends React.Component {
  render() {
    const {count, group, groupBy} = this.props;
    const label = this.props.labels[groupBy][group];

    return (
      <ListItem count={count} label={label} />
    );
  }
}
