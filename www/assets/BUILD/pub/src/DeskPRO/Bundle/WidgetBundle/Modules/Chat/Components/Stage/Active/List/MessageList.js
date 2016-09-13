import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar-versioned';
import { MessageFactoryContainer } from './MessageFactoryContainer';
import { TypingEventContainer } from './Event/TypingEventContainer';
import '../../../../../../Resources/sounds/pop.mp3';
import '../../../../../../Resources/sounds/pop.ogg';
import '../../../../../../Resources/sounds/pop.wav';

export class MessageList extends React.Component {

  static propTypes = {
    messages:      PropTypes.object,
    lastMessageId: PropTypes.number,
    mute:          PropTypes.bool,
    isEnded:       PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      messagesCount: 0,
      lastMessageId: null
    };
  }

  componentDidMount() {
    this.canPlaySound = false;
    this.checkForNewMessages();
  }

  componentDidUpdate() {
    this.checkForNewMessages();
  }

  onUpdateList = () => {
    this.checkForNewMessages(true);
  };

  checkForNewMessages(force) {
    const { messages, lastMessageId, mute } = this.props;
    if (messages.size !== this.state.messagesCount || force) {
      this.setState({
        messagesCount: messages.size,
        lastMessageId
      });

      setTimeout(() => this.scrollArea && this.scrollArea.scrollBottom(), 0);

      // Checking for agent messages to play sound notification
      const newAgentMessage = messages.filter(message =>
        message.get('id') > this.state.lastMessageId
        && !message.get('is_user')
        && !message.get('is_sys')
      );

      // Don't play sound on initial load
      if (this.canPlaySound && !mute && newAgentMessage.size > 0) {
        try {
          this.sound.play();
        } catch (e) {
          console.warn('Unable to play sound');
        }
      }

      this.canPlaySound = true;
    }
  }

  refresh() {
    const scrollArea = this.scrollArea;
    if (scrollArea) {
      scrollArea.setSizesToState();
      scrollArea.handleWindowResize();
    }
  }

  render() {
    return (
      <div>
        <audio ref={(c) => { this.sound = c; }} preload="preload">
          <source
            src={`${window.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO/Bundle/WidgetBundle/Resources/sounds/pop.mp3`}
          />
          <source
            src={`${window.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO/Bundle/WidgetBundle/Resources/sounds/pop.ogg`}
          />
          <source
            src={`${window.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO/Bundle/WidgetBundle/Resources/sounds/pop.wav`}
          />
        </audio>
        <ScrollArea ref={(c) => { this.scrollArea = c; }} ownerDocument={window.widgetFrame.document} vertical>
          <div className="bottom-aligner" />
          <div>
            {this.props.messages.map((message, key) => <MessageFactoryContainer key={key} message={message} />)}
            <TypingEventContainer onUpdate={this.onUpdateList} />
          </div>
        </ScrollArea>
      </div>
    );
  }
}
export default MessageList;
