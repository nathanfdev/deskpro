import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { windowResize } from '../../../../../../Application/Actions/dpWindowActions';
import { endChat, unsetChatId } from '../../../../../Actions/chatActions';
import { history } from '../../../../../../../Services/history';

export class WaitingAgent extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func,
    chatId:        PropTypes.string,
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
    this.props.dispatch(windowResize());
    this.setState({
      buttonShown: true
    });
  };

  onOpenTicketForm = (event) => {
    event.preventDefault();

    const { chatId, dispatch } = this.props;

    dispatch(endChat(chatId));
    dispatch(unsetChatId());

    history.replace('/ticket/form');
  };

  render() {
    const { buttonShown } = this.state;

    return (
      <div className="dpdesignportal-collect-user-info-header">
        <span className="img" />
        <span className="text">{portalPhrases.get('portal.chat.message_wait-pending')}</span>

        {buttonShown &&
          <div className="dpdesignportal-chat-message-long">
            <p>{portalPhrases.get('portal.chat.message_wait-long')}</p>
            <p>
              <a href="#open-ticket-form" onClick={this.onOpenTicketForm}>
                {portalPhrases.get('portal.chat.message_wait-ticket')}
              </a>
            </p>
          </div>
        }
      </div>
    );
  }
}
