import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import ScrollArea from 'react-scrollbar';
import { MessageFactoryContainer } from './MessageFactoryContainer';
import { TypingEventContainer } from './Event/TypingEventContainer';
import '../../../../../../Resources/sounds/pop.mp3';
import '../../../../../../Resources/sounds/pop.ogg';
import '../../../../../../Resources/sounds/pop.wav';

export class MessageList extends React.Component {

  static propTypes = {
    messages: PropTypes.object,
    mute:     PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      messages: props.messages
    };
  }

  componentWillReceiveProps(newProps) {
    this.setState({ messages: newProps.messages });
  }

  shouldComponentUpdate(props, newState) {
    return !Immutable.is(newState.messages, this.state.messages);
  }

  componentWillUpdate(props, newState) {
    let lastId = 0;
    if (this.state.messages.size) {
      lastId = this.state.messages.last().get('id');
    }

    for (let i = newState.messages.size - 1; i >= 0; i -= 1) {
      const message = newState.messages.get(i);

      if (lastId < message.get('id') && !message.get('is_user') && !message.get('is_sys')) {
        this.shouldPlaySound = true;
        break;
      }

      if (lastId >= message.get('id')) {
        break;
      }
    }
  }

  componentDidUpdate() {
    this.scrollBottom();
    this.playSound();
  }

  scrollBottom = () => setTimeout(() => {
    if (this.scrollArea) {
      this.scrollArea.setSizesToState();
      this.scrollArea.handleWindowResize();
      this.scrollArea.scrollBottom();
    }
  }, 0);

  playSound() {
    const { mute } = this.props;

    if (!this.shouldPlaySound || mute) {
      return;
    }

    try {
      this.sound.play();
    } catch (e) {
      console.warn('Unable to play sound');
    }

    this.shouldPlaySound = false;
  }

  render() {
    const { messages } = this.state;
    const soundsPath = `${window.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO/Bundle/WidgetBundle/Resources/sounds`;

    return (
      <div ref={(node) => { this.node = node; }}>
        <audio ref={(c) => { this.sound = c; }} preload="preload">
          <source src={`${soundsPath}/pop.mp3`} />
          <source src={`${soundsPath}/pop.ogg`} />
          <source src={`${soundsPath}/pop.wav`} />
        </audio>
        <ScrollArea
          ref={(c) => { this.scrollArea = c; }}
          ownerDocument={window.widgetFrame.document}
          vertical
          style={{ overflow: 'hidden' }}
        >
          <div className="bottom-aligner" />
          <div>
            {messages.map((message, key) => <MessageFactoryContainer key={key} message={message} />)}
            <TypingEventContainer onUpdate={this.scrollBottom} />
          </div>
        </ScrollArea>
      </div>
    );
  }
}
export default MessageList;
