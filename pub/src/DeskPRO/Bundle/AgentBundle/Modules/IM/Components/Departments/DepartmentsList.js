import React, {Component, PropTypes} from 'react';
import { DepartmentsListItem } from './DepartmentsListItem';
import { connect } from 'react-redux';

// departmetns
import { myDepartmentsSelector, myDepartmentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  departments: myDepartmentsSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state)
}))
export class DepartmentsList extends Component {

  static propTypes = {
    departments: PropTypes.object.isRequired,
    departmentsStatus: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.departments.map((department, index) => {
            return (
              <DepartmentsListItem
                dispatch={this.props.dispatch}
                key={index}
                department={department}
              />);
          })
        }
      </ul>
    );
  }
}