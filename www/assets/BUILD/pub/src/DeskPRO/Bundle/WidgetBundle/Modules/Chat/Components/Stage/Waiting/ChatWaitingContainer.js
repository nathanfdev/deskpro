import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { WaitingPreview } from './WaitingPreview';
import { endChat, unsetChatId } from '../../../Actions/chatActions';
import { chatIdSelector } from '../../../Selectors/chat';
import { agentAcceptTimeoutSelector } from '../../../../Application/Selectors/dpWindow';
import { history } from '../../../../../Services/history';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

@connect(state => ({
  chatId: chatIdSelector(state),
  acceptTimeout: agentAcceptTimeoutSelector(state)
}))
export class ChatWaitingContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
    acceptTimeout: PropTypes.number
  };

  constructor(props) {
    super(props);
    this.state = {
      buttonShown: false
    };
  }

  componentDidMount() {
    this.timeout = setTimeout(this.onShowButton, this.props.acceptTimeout * 1000);
  }

  componentWillUnmount() {
    clearTimeout(this.timeout);
  }

  onShowButton = () => {
    this.setState({
      buttonShown: true
    });
  };

  onOpenTicketForm = event => {
    event.preventDefault();

    const { chatId, dispatch } = this.props;
    dispatch(endChat(chatId));
    dispatch(unsetChatId());

    history.replace('/ticket/form');
  };

  render() {
    // portal.chat.message_wait-ticket
    return (
      <div>
        <WaitingPreview />
        {this.state.buttonShown &&
          <span>
            <p>{portalPhrases.get('portal.chat.message_wait-long')}</p>
            <p>Would you like to <a href="#" onClick={this.onOpenTicketForm}>submit</a> a ticket instead?</p>
          </span>
        }
      </div>
    );
  }
}
