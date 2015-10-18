import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import SimplePositioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Simple';
import { Overlay } from './Overlay';
import { Chat } from './ChatWindow/Chat';

@connect(state => ({
  current: state.IM.ui.get('current'),
  chating: state.IM.ui.get('chating'),
  overlayShown: state.IM.ui.get('overlayShown'),
}))
export class IMContainer extends React.Component {

  static propTypes = {
    current: PropTypes.object.isRequired,
    chating: PropTypes.bool.isRequired,
    overlayShown: PropTypes.bool.isRequired
  };

  renderOverlay = () => {
    return (
      <SimplePositioned
        positionMy="left-25 top+1"
        positionAt="center bottom"
        collision="none"
        positionTarget={document.getElementById('im-button')}
        isOpen={this.props.overlayShown}
        >
        <Overlay />
      </SimplePositioned>
    );
  };

  renderChat = () => {
    const id = 'chat-with-' + this.props.current.get('chat_type') + '-' + this.props.current.get('id');
    console.log(id);
    let node = document.getElementById(id);
    if (!node) {
      node = document.getElementById('im-button');
    }
    return (
      <SimplePositioned
        positionMy="left-25 top+1"
        positionAt="center bottom"
        collision="none"
        positionTarget={node}
        isOpen={this.props.chating}
      >
        <Chat />
      </SimplePositioned>
      );
  };

  render() {
    return (
     <div id="im-container">
       { this.renderOverlay() }
       { this.renderChat() }
     </div>
    );
  }
}