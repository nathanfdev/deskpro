import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class EndChatButton extends React.Component {

  static propTypes = {
    onOpenPopup: PropTypes.func
  };

  render() {
    return (
      <div className="dpdesignportal-chat-form-button-row-end-chat">
        <a href="#" className="dpdesignportal-chat-form-button" onClick={this.props.onOpenPopup}>
          <i className="fa fa-upload" />{portalPhrases.get('portal.chat.end_chat')}
        </a>
      </div>
    );
  }
}
