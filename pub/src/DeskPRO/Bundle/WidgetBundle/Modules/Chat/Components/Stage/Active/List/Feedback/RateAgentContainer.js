import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { RateAgentDialog } from './RateAgentDialog';
import { RateAgentComplete } from './RateAgentComplete';
import { RateAgentForm } from './RateAgentForm';
import { sendFeedback } from '../../../../../Actions/chatActions';

@connect()
export class RateAgentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      stage: 'dialog'
    };
  }

  onClickHelpful = () => {
    this.props.dispatch(sendFeedback());
    this.setState({
      stage: 'finished'
    });
  };

  onClickNotHelpful = () => {
    this.setState({
      stage: 'form'
    });
  };

  onSubmitForm = () => {
    this.props.dispatch(sendFeedback());
    this.setState({
      stage: 'finished'
    });
  };

  render() {
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
