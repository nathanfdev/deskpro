import React from 'react';
import { connect } from 'react-redux';
import { Controls } from './Controls';
import { isEndedSekector } from '../../../../../Selectors/chat';

@connect(state => ({
  isEnded: isEndedSekector(state)
}))
export class ControlsContainer extends React.Component {

  render() {
    return <Controls {...this.props} />;
  }
}
