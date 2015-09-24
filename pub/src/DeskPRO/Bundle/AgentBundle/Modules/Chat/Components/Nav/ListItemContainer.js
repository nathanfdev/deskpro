import React from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { agentNamesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { createDepartmentsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Selectors/departmentsSelectors';
import { reduceMapToProperty } from 'DeskPRO/Component/Util/Map';

const chatNavDepartmentsSelector = createDepartmentsRequestSelectors('chatNav');

@connect(state => ({
  labels: {
    agent: agentNamesSelector(state),
    department: reduceMapToProperty('title', chatNavDepartmentsSelector.recordsSel(state).toJS()),
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
