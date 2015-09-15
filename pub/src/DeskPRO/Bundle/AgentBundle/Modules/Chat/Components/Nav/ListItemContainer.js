import React from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

@connect(state => ({
  labels: {
    agent: state.Chat.nav.get('agentNames').toJS(),
    department: state.Chat.nav.get('departmentNames').toJS(),
    date_period: DatePeriods.all
  }
}))
export class ListItemContainer extends React.Component {
  render() {
    const {count, group, groupBy, onClick} = this.props;
    const label = this.props.labels[groupBy][group] ? this.props.labels[groupBy][group] : '...';
    const onItemClick = () => { onClick({[groupBy]: group}); };

    return (
      <ListItem count={count} label={label} onClick={onItemClick} />
    );
  }
}
