import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import { IMOverlay } from './IMOverlay';
import { Chat } from './ChatWindow/Chat';
import { ChatHelper } from '../../../Services/Helpers/ChatHelper';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

@connect(state => ({
  current:      state.IM.chats.get('current'),
  chating:      state.IM.chats.get('chating'),
  overlayShown: state.IM.chats.get('overlayShown'),
  user:         meSelector(state)
}))
export class IMContainer extends React.Component {

  static propTypes = {
    current:      PropTypes.object.isRequired,
    chating:      PropTypes.bool.isRequired,
    overlayShown: PropTypes.bool.isRequired,
    dispatch:     PropTypes.func.isRequired,
    user:         PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.helper = new ChatHelper();
  }

  renderOverlay = function () {
    return (
      <Simple
        positionMy="left-25 top+1"
        positionAt="center bottom"
        collision="none"
        positionTarget={document.getElementById('im-button')}
        isOpen={this.props.overlayShown}
      >
        <IMOverlay dispatch={this.props.dispatch} />
      </Simple>
    );
  };

  renderChat = () => {
    const node = this.helper.getChatNode(this.props.current, this.props.user.get('id'));
    return (
      <Simple
        positionMy="left-25 top+8"
        positionAt="center bottom"
        collision="none"
        positionTarget={node}
        isOpen={this.props.chating}
      >
        <Chat />
      </Simple>
      );
  };

  render() {
    return (
      <div id="im-container">
        {this.renderOverlay()}
        {this.renderChat()}
      </div>
    );
  }
}
