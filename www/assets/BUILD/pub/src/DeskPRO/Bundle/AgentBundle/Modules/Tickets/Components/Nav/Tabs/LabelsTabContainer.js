import React, { Component, PropTypes } from 'react';
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

  onLabelClick = (params) => {
    this.props.dispatch(applyListParams({ label: [params.value] }));
  };

  render() {
    return <LabelsTab {...this.props} onLabelClick={this.onLabelClick} />;
  }
}
