import React, { Component, PropTypes } from 'react';
import { AssignDepartment } from './AssignDepartment';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

import { connect } from 'react-redux';
@connect(state => ({
  me: meSelector(state),
}))

export class AssignDepartmentContainer extends Component {

  render() {
    return (
      <AssignDepartment {...this.props} />
    );
  }
}