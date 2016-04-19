import React, { Component } from 'react';
import { AssignDepartment } from './AssignDepartment';

export class AssignDepartmentContainer extends Component {

  render() {
    return (
      <AssignDepartment {...this.props} />
    );
  }
}
