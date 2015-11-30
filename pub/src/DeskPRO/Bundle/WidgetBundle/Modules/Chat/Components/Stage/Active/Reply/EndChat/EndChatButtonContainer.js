import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { EndChatButton } from './EndChatButton';

@connect()
export class EndChatButtonContainer extends React.Component {

  onEndChat = () => {
    console.log('onEndChat');
  };

  render() {
    return <EndChatButton {...this.props} onEndChat={this.onEndChat} />;
  }
}
