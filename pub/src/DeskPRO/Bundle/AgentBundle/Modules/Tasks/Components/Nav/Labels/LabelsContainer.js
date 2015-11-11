import React from 'react';
import { connect } from 'react-redux';
import { Labels } from './Labels';
import { allTaskLabelsSelector } from '../../../RecordStores/Selectors/taskLabelSelectors';

@connect(state => ({
  labels: allTaskLabelsSelector(state)
}))
export class LabelsContainer extends React.Component {

  render() {
    return <Labels {...this.props} />;
  }
}
