import React from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { createPeopleNamesRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Selectors/peopleNamesSelectors';
import { createDepartmentsNamesRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Selectors/departmentsNamesSelectors';

const chatNavPeopleNamesSelector = createPeopleNamesRequestSelectors('chatNav');
const chatNavDepartmentsNamesSelector = createDepartmentsNamesRequestSelectors('chatNav');

/**
 * Reduces record stores {id: {id, name}} to {id: name}
 * @param records
 * @return Object {id: name}
 */
function reduceToName(records) {
  const reduced = {};
  Object.keys(records).forEach(key => reduced[key] = records[key].name);

  return reduced;
}

@connect(state => ({
  labels: {
    agent: reduceToName(chatNavPeopleNamesSelector.recordsSel(state).toJS()),
    department: reduceToName(chatNavDepartmentsNamesSelector.recordsSel(state).toJS()),
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
