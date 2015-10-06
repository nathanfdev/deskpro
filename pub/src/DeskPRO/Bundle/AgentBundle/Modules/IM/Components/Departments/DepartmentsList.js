import React, {Component, PropTypes} from 'react';
import { DepartmentsListItem } from './DepartmentsListItem';

import { connect } from 'react-redux';

import { loadDepartments, loadAllDepartments }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { departmentsSelector, allDepartmentsSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  departments: allDepartmentsSelector(state),

}))
export class DepartmentsList extends Component {

  static propTypes = {
    departments: PropTypes.object.isRequired
  };

  componentWillMount() {
    "use strict";
    this.props.dispatch(loadAllDepartments());
  }

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.departments.map((department, index) => <DepartmentsListItem key={index} department={department}/>)
        }
      </ul>
    );
  }
}