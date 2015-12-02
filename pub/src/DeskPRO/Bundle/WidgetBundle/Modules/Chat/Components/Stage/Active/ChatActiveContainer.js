import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Header } from './Header/Header';
import { MessagesListContainer } from './List/MessagesListContainer';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';
import { RateAgentContainer } from './Feedback/RateAgentContainer';
import { isEndedSelector } from '../../../Selectors/chat';

@connect(state => ({
  isEnded: isEndedSelector(state)
}))
export class ChatActiveContainer extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool
  };

  render() {
    const { isEnded } = this.props;

    return (
      <div>
        <Header />
        <MessagesListContainer />
        {isEnded && <RateAgentContainer />}
        <ReplyFormContainer />
      </div>
    );
  }
}
