import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { MuteButton } from './MuteButton';
import { muteSelector } from '../../../../../../Selectors/chat';
import { toggleMute } from '../../../../../../Actions/chatActions';

@connect(state => ({
  enabled: muteSelector(state)
}))
export class MuteContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  onClick = () => {
    this.props.dispatch(toggleMute());
  };

  render() {
    return <MuteButton {...this.props} onClick={this.onClick} />;
  }
}
