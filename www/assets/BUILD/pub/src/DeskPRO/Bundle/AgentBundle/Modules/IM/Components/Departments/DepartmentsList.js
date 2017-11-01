import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { DepartmentsListItem } from './DepartmentsListItem';
import { connect } from 'react-redux';
import { myTicketsDepartmentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  departments: myTicketsDepartmentsSelector(state)
}))
export class DepartmentsList extends Component {
  static propTypes = {
    departments: PropTypes.object.isRequired,
    dispatch:    PropTypes.func.isRequired
  };

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.departments.map((department, index) =>
              <DepartmentsListItem
                dispatch={this.props.dispatch}
                key={index}
                department={department}
              />
          )
        }
      </ul>
    );
  }
}
