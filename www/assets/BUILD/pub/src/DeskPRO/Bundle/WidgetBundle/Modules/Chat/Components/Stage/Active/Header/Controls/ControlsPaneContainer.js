import React from 'react';
import { connect } from 'react-redux';
import { ControlsPane } from './ControlsPane';
import { isEndedSelector } from '../../../../../Selectors/chat';

@connect(state => ({
  isEnded: isEndedSelector(state)
}))
export class ControlsPaneContainer extends React.Component {

  render() {
    return <ControlsPane {...this.props} />;
  }
}
