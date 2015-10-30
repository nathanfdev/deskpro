import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { TabSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { labelsSelector, isDoneSelector } from '../../../Selectors/nav';
import { LabelsTab } from './LabelsTab';

@connect(state => ({
  isDone: isDoneSelector('labels')(state),
  labels: labelsSelector(state)
}))
export class LabelsTabContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    return this.props.isDone ? <LabelsTab {...this.props} /> : <TabSpinner />;
  }
}
