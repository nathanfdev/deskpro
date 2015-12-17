import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import ScrollArea from 'react-scrollbar';
import { MessageFactory } from './MessageFactory';
import popMp3 from '../../../../../../Resources/sounds/pop.mp3';
import popOgg from '../../../../../../Resources/sounds/pop.ogg';
import popWav from '../../../../../../Resources/sounds/pop.wav';

export class MessagesList extends React.Component {

  static propTypes = {
    messages: PropTypes.object,
    lastMessageId: PropTypes.number,
    mute: PropTypes.bool,
    isEnded: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      lastMessageId: null
    };
  }

  componentDidMount() {
    this.scrollBottom();
    this.checkForNewMessages();
  }

  componentDidUpdate() {
    this.scrollBottom();
    this.checkForNewMessages();
  }

  refresh() {
    const scrollArea = this.refs.scrollArea;

    scrollArea.setSizesToState();
    scrollArea.handleWindowResize();
  }

  checkForNewMessages() {
    const { lastMessageId, mute } = this.props;
    if (lastMessageId !== this.state.lastMessageId) {
      this.setState({
        lastMessageId: lastMessageId
      });

      if (!mute) {
        const sound = ReactDOM.findDOMNode(this.refs.sound);
        try {
          sound.play();
        } catch (e) {
          console.warn('Unable to play sound');
        }
      }
    }
  }

  scrollBottom() {
    if (this.refs.scrollArea) {
      setTimeout(() => this.refs.scrollArea.scrollBottom(), 0);
    }
  }

  render() {
    const { messages } = this.props;

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
            {messages.map((message, key) => <MessageFactory key={key} message={message} />)}
          </div>
        </ScrollArea>
      </div>
    );
  }
}
