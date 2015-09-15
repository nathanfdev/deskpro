import React from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

@connect(state => ({
  labels: {
    agent: state.Chat.nav.agentNames,
    department: state.Chat.nav.departmentNames,
    date_period: DatePeriods.all
  }
}))
export class ChatsListItem extends React.Component {
  getLabel(groupBy, group) {
    if (this.props.labels[groupBy] && this.props.labels[groupBy][group]) {
      return this.props.labels[groupBy];
    }
    return '[no label]';
  }
  render() {
    const {count, group, groupBy, onClick} = this.props;
    const label = this.getLabel(groupBy, group);
    const onItemClick = () => { onClick({[groupBy]: group}) };

    return (
      <ListItem count={count} label={label} onClick={onItemClick} />
    );
  }
}
