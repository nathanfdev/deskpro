import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { RateAgentDialog } from './RateAgentDialog';
import { RateAgentComplete } from './RateAgentComplete';
import { RateAgentForm } from './RateAgentForm';
import { sendFeedback, showNotHelpfulForm } from '../../../../Actions/chatActions';
import { chatIdSelector, isEndedSelector, feedbackStageSelector, agentNameSelector } from '../../../../Selectors/chat';

@connect(state => ({
  chatId:    chatIdSelector(state),
  isEnded:   isEndedSelector(state),
  stage:     feedbackStageSelector(state),
  agentName: agentNameSelector(state)
}))
export class RateAgentContainer extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    chatId:    PropTypes.number,
    isEnded:   PropTypes.bool,
    stage:     PropTypes.string,
    dispatch:  PropTypes.func.isRequired
  };

  onClickHelpful = () => {
    const { chatId, dispatch } = this.props;
    dispatch(sendFeedback(chatId, { helpful: 10 }));
  };

  onClickNotHelpful = () => {
    const { dispatch } = this.props;
    dispatch(showNotHelpfulForm());
  };

  onSubmitForm = comment => {
    const { chatId, dispatch } = this.props;
    dispatch(sendFeedback(chatId, { helpful: 1, comment }));
  };

  render() {
    const { stage, isEnded, agentName } = this.props;
    if (!isEnded) {
      return null;
    }

    switch (stage) {
      case 'finished':
        return <RateAgentComplete />;
      case 'form':
        return <RateAgentForm agentName={agentName} onSubmit={this.onSubmitForm} />;
      case 'dialog':
        return (
          <RateAgentDialog
            agentName={agentName}
            onClickHelpful={this.onClickHelpful}
            onClickNotHelpful={this.onClickNotHelpful}
          />
        );
      default:
        return null;
    }
  }
}
