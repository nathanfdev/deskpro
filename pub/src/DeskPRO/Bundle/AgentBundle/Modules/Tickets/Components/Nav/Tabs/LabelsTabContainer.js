import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { labelsSelector, isDoneSelector } from '../../../Selectors/nav';
import { LabelsTab } from './LabelsTab';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

@connect(state => ({
  isDone: isDoneSelector(state),
  labels: labelsSelector(state)
}))
export class LabelsTabContainer extends Component {

  static propTypes = {
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <LoadIndicator loaded={this.props.isDone}>
        <LabelsTab {...this.props} />
      </LoadIndicator>
    );
  }
}
