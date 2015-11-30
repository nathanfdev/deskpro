import React from 'react';
import { connect } from 'react-redux';
import { Controls } from './Controls';
import { isEndedSelector } from '../../../../../Selectors/chat';

@connect(state => ({
  isEnded: isEndedSelector(state)
}))
export class ControlsContainer extends React.Component {

  render() {
    return <Controls {...this.props} />;
  }
}
