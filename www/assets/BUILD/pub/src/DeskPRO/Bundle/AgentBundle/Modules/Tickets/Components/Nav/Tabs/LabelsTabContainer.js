import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { labelsSelector } from '../../../Selectors/nav';
import { LabelsTab } from './LabelsTab';
import { applyListParams } from '../../../Actions/listActions';

@connect(state => ({
  labels: labelsSelector(state)
}))
export class LabelsTabContainer extends Component {
  static propTypes = {
    labels:   PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return <LabelsTab {...this.props} onClick={applyListParams} />;
  }
}
