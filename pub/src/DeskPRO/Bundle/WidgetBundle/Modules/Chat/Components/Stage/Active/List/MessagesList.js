import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import ScrollArea from 'react-scrollbar';
import { MessageFactoryContainer } from './MessageFactoryContainer';
import popMp3 from '../../../../../../Resources/sounds/pop.mp3';
import popOgg from '../../../../../../Resources/sounds/pop.ogg';
import popWav from '../../../../../../Resources/sounds/pop.wav';

export class MessagesList extends React.Component {

  static propTypes = {
    chatLoaded: PropTypes.bool,
    messages: PropTypes.object,
    lastMessageId: PropTypes.number,
    mute: PropTypes.bool,
    isEnded: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      messagesCount: 0,
      lastMessageId: null
    };
  }

  componentDidMount() {
    this.checkForNewMessages();
  }

  componentDidUpdate() {
    this.checkForNewMessages();
  }

  refresh() {
    const scrollArea = this.refs.scrollArea;
    if (scrollArea) {
      scrollArea.setSizesToState();
      scrollArea.handleWindowResize();
    }
  }

  checkForNewMessages() {
    const { chatLoaded, messages, lastMessageId, mute } = this.props;
    if (messages.size !== this.state.messagesCount) {
      this.setState({
        messagesCount: messages.size,
        lastMessageId: lastMessageId
      });

      if (this.refs.scrollArea) {
        this.refs.scrollArea.scrollBottom();
      }

      // Checking for agent messages
      const newAgentMessage = messages.filter(message => {
        return message.get('id') > this.state.lastMessageId && message.get('author_type') === 'agent';
      });

      // Don't play sound on initial load
      if (chatLoaded && !mute && newAgentMessage.size > 0) {
        const sound = ReactDOM.findDOMNode(this.refs.sound);
        try {
          sound.play();
        } catch (e) {
          console.warn('Unable to play sound');
        }
      }
    }
  }

  render() {
    const { messages, chatLoaded } = this.props;

    if (!chatLoaded) {
      return (
        <div className="dpdesignportal-content">
          <div className="circle-spinner chat-message-list"><i/></div>
        </div>
      );
    }

    return (
      <div className="dpdesignportal-content">
        <audio ref="sound" preload="preload">
          <source src={popMp3} />
          <source src={popOgg} />
          <source src={popWav} />
        </audio>
        <ScrollArea ref="scrollArea" vertical>
          <div className="bottom-aligner"/>
          <div>
            {messages.map((message, key) => <MessageFactoryContainer key={key} message={message} />)}
          </div>
        </ScrollArea>
      </div>
    );
  }
}
