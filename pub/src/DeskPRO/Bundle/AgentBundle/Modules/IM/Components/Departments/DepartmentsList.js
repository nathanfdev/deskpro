import React, {Component, PropTypes} from 'react';
import { DepartmentsListItem } from './DepartmentsListItem';

import { connect } from 'react-redux';

import { loadMyDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { myDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  departments: myDepartmentsSelector(state),
  me: state.Application.user
}))
export class DepartmentsList extends Component {

  static propTypes = {
    departments: PropTypes.object.isRequired,
    me: PropTypes.object.isRequired
  };

  componentWillMount() {
    this.props.dispatch(loadMyDepartments());
  }

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.departments.map((department, index) => {
            "use strict";
            return <DepartmentsListItem
              handleClickParticipant={this.props.handleClickParticipant}
              key={index}
              department={department}
              />
          })
        }
      </ul>
    );
  }
}