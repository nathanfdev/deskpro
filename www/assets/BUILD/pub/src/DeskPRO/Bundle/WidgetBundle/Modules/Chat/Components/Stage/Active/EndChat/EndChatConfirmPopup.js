import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class EndChatConfirmPopup extends React.Component {

  static propTypes = {
    locked: PropTypes.bool,
    positionAt: PropTypes.string,
    onCancel: PropTypes.func,
    onConfirm: PropTypes.func
  };

  render() {
    const { onCancel, onConfirm, locked, positionAt } = this.props;

    return (
      <div className={classNames('dpdesignportal-popover', 'dpdesignportal-popover-end-chat', positionAt)}>
        <h1>{portalPhrases.get('portal.chat.end_chat_confirm_title')}</h1>
        <p className="grey">{portalPhrases.get('portal.chat.end_chat_confirm_desc')}</p>

        <div className="popover-buttons">
          <a href="#" className={classNames('dpdesignportal-button', {'locked': locked})} onClick={onConfirm}>
            <i className="fa fa-power-off"></i> {portalPhrases.get('portal.chat.end_chat')}
          </a>
          <a href="#" className="dpdesignportal-button grey" onClick={onCancel}>
            <i className="fa fa-reply"></i> {portalPhrases.get('portal.chat.cancel_end_chat')}
          </a>
        </div>
      </div>
    );
  }
}
