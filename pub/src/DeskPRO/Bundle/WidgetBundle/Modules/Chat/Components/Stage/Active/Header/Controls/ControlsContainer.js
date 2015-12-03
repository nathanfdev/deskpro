import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ActivePane } from './Active/ActivePane';
import { DonePane } from './Done/DonePane';
import { isEndedSelector } from '../../../../../Selectors/chat';

@connect(state => ({
  isEnded: isEndedSelector(state)
}))
export class ControlsContainer extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool
  };

  render() {
    return this.props.isEnded ? <DonePane /> : <ActivePane />;
  }
}
