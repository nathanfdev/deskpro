import React from 'react';
import { connect } from 'redux/react';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

@connect(state => ({
  labels: {
    agent: state.ChatNav.agentNames,
    department: state.ChatNav.departmentNames,
    date_period: DatePeriods.all
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
