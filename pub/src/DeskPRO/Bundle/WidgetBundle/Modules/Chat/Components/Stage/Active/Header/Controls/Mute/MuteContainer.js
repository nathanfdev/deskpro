import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { MuteButton } from './MuteButton';
import { audioNotificationsSelector } from '../../../../../../Selectors/chat';
import { toggleAudioNotifications } from '../../../../../../Actions/chatActions';

@connect(state => ({
  enabled: audioNotificationsSelector(state)
}))
export class MuteContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  onClick = () => {
    this.props.dispatch(toggleAudioNotifications());
  };

  render() {
    return <MuteButton {...this.props} onClick={this.onClick} />;
  }
}
