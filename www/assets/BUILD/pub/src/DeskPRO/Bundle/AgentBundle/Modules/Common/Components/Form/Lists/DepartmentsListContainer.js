import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { DepartmentsList } from './DepartmentsList';

import { connect } from 'react-redux';
@connect(state => ({
  values: collectionSelectorFactory('Department', 'all_tickets')(state)
}))

export class DepartmentsListContainer extends Component {
  render() {
    return (
      <DepartmentsList {...this.props} />
    );
  }
}
