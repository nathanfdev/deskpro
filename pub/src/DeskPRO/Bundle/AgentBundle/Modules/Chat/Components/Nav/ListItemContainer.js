import React from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createDepartmentsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Selectors/departmentsSelectors';

const chatNavUsersSelector = createPeopleRequestSelectors('chatNav');
const chatNavDepartmentsSelector = createDepartmentsRequestSelectors('chatNav');

/**
 * Reduces record stores to property value {id: {...}} to {id: property}
 * @param property
 * @param records
 * @return Object {id: property}
 */
function reduceTo(property, records) {
  const reduced = {};
  Object.keys(records).forEach(key => reduced[key] = records[key][property]);

  return reduced;
}

@connect(state => ({
  labels: {
    agent: reduceTo('name', chatNavUsersSelector.recordsSel(state).toJS()),
    department: reduceTo('title', chatNavDepartmentsSelector.recordsSel(state).toJS()),
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
