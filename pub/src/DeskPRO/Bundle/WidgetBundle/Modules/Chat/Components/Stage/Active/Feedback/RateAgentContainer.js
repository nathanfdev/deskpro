import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { RateAgentDialog } from './RateAgentDialog';
import { RateAgentComplete } from './RateAgentComplete';
import { RateAgentForm } from './RateAgentForm';
import { sendFeedback } from '../../../../Actions/chatActions';
import { chatIdSelector, isEndedSelector } from '../../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  isEnded: isEndedSelector(state)
}))
export class RateAgentContainer extends React.Component {

  static propTypes = {
    chatId: PropTypes.number,
    isEnded: PropTypes.bool,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      stage: 'dialog'
    };
  }

  onClickHelpful = () => {
    const { chatId, dispatch } = this.props;

    dispatch(sendFeedback(chatId, {helpful: 10}));
    this.setState({
      stage: 'finished'
    });
  };

  onClickNotHelpful = () => {
    this.setState({
      stage: 'form'
    });
  };

  onSubmitForm = comment => {
    const { chatId, dispatch } = this.props;

    dispatch(sendFeedback(chatId, {helpful: 1, comment}));
    this.setState({
      stage: 'finished'
    });
  };

  render() {
    if (!this.props.isEnded) {
      return null;
    }

    switch (this.state.stage) {
      case 'finished':
        return <RateAgentComplete />;
      case 'form':
        return <RateAgentForm onSubmit={this.onSubmitForm} />;
      case 'dialog':
        return (
          <RateAgentDialog onClickHelpful={this.onClickHelpful}
                           onClickNotHelpful={this.onClickNotHelpful} />
        );
      default:
        return null;
    }
  }
}
