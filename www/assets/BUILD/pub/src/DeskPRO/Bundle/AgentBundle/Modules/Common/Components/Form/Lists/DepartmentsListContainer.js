import React, { Component, PropTypes } from 'react';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { DepartmentsList } from './DepartmentsList';

import { connect } from 'react-redux';
@connect(state => ({
  values: allSelectorFactory('Department')(state)
}))

export class DepartmentsListContainer extends Component {
  render() {
    return (
      <DepartmentsList {...this.props} />
    );
  }
}
