import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { labelsSelector } from '../../../Selectors/nav';
import { LabelsTab } from './LabelsTab';

@connect(state => ({
  labels: labelsSelector(state)
}))

export class LabelsTabContainer extends Component {
  static propTypes = {
    labels: PropTypes.object.isRequired
  };

  render() {
    return <LabelsTab {...this.props} />;
  }
}
