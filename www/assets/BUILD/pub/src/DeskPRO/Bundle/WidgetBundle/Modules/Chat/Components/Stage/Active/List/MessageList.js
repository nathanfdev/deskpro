import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { MessageFactoryContainer } from './MessageFactoryContainer';
import { TypingEventContainer } from './Event/TypingEventContainer';
import '../../../../../../Resources/sounds/pop.mp3';
import '../../../../../../Resources/sounds/pop.ogg';
import '../../../../../../Resources/sounds/pop.wav';
import Immutable from 'immutable';

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

  shouldComponentUpdate(props, state) {
    return !Immutable.is(state.messages, this.state.messages);
  }

  componentWillUpdate(props, state) {
    let lastId = 0;
    if (this.state.messages.size) {
      lastId = this.state.messages.last().get('id');
    }

    for (let i = state.messages.size - 1; i >= 0; i--) {
      const message = state.messages.get(i);

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

  scrollBottom = () => {
    const node = ReactDOM.findDOMNode(this);
    node.scrollTop = node.scrollHeight;
  };

  playSound() {
    const { mute } = this.props;

    if (!this.shouldPlaySound || mute) {
      return;
    }

    const sound = ReactDOM.findDOMNode(this.refs.sound);
    try {
      sound.play();
    } catch (e) {
      console.warn('Unable to play sound');
    }

    this.shouldPlaySound = false;
  }

  render() {
    return (
      <div style={{ overflowY: 'auto' }}>
        <audio ref="sound" preload="preload">
          <source src={`${window.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO/Bundle/WidgetBundle/Resources/sounds/pop.mp3`} />
          <source src={`${window.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO/Bundle/WidgetBundle/Resources/sounds/pop.ogg`} />
          <source src={`${window.DESKPRO_APP_ASSETS_URL}/pub/build/DeskPRO/Bundle/WidgetBundle/Resources/sounds/pop.wav`} />
        </audio>
        {this.state.messages.map((message, key) => <MessageFactoryContainer key={key} message={message} />)}
        <TypingEventContainer onUpdate={this.scrollBottom} />
      </div>
    );
  }
}
