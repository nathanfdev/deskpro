import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { isEndedSelector } from '../../../../../../Selectors/chat';
import { EndChatContainer } from '../../../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import { ReopenChatContainer } from '../../../ReopenChatContainer';
import { ReopenChatButton } from './ReopenChatButton';

@connect(state => ({
  isEnded: isEndedSelector(state)
}))
export class EndChatButtonContainer extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool
  };

  render() {
    return this.props.isEnded
      ?
      <ReopenChatContainer>
        <ReopenChatButton />
      </ReopenChatContainer>
      :
      <EndChatContainer confirmPosition="bottom">
        <EndChatButton />
      </EndChatContainer>
    ;
  }
}
