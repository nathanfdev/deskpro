import React, { PropTypes } from 'react';
import { windowResize } from '../../../../../../Application/Actions/dpWindowActions';
import { endChat, unsetChatId } from '../../../../../Actions/chatActions';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { history } from '../../../../../../../Services/history';

export class WaitingAgent extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func,
    chatId:        PropTypes.number,
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

  onOpenTicketForm = event => {
    event.preventDefault();

    const { chatId, dispatch } = this.props;
    dispatch(endChat(chatId));
    dispatch(unsetChatId());

    history.replace('/ticket/form');
  };

  render() {
    return (
      <div className="dpdesignportal-collect-user-info-header">
        <span className="img" />
        <span className="text">{portalPhrases.get('portal.chat.message_wait-pending')}</span>

        {this.state.buttonShown &&
          <div className="dpdesignportal-chat-message-long">
            <p>{portalPhrases.get('portal.chat.message_wait-long')}</p>
            <p>
              <a href="#" onClick={this.onOpenTicketForm}>
                {portalPhrases.get('portal.chat.message_wait-ticket')}
              </a>
            </p>
          </div>
        }
      </div>
    );
  }
}
