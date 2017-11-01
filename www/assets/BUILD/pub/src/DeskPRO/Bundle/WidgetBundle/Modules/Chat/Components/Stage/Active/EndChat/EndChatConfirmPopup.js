import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class EndChatConfirmPopup extends React.Component {

  static propTypes = {
    locked:     PropTypes.bool,
    positionAt: PropTypes.string,
    onCancel:   PropTypes.func,
    onConfirm:  PropTypes.func
  };

  render() {
    const { onCancel, onConfirm, locked, positionAt } = this.props;

    return (
      <div className={classNames('dpdesignportal-popover', 'dpdesignportal-popover-end-chat', positionAt)}>
        <h1>{portalPhrases.get('portal.chat.end_chat_confirm_title')}</h1>

        <div className="popover-buttons">
          <button className={classNames('dpdesignportal-button', { locked })} onClick={onConfirm}>
            <i className="fa fa-power-off" /> {portalPhrases.get('portal.chat.end_chat')}
          </button>
          <button className="dpdesignportal-button grey" onClick={onCancel}>
            <i className="fa fa-reply" /> {portalPhrases.get('portal.chat.cancel_end_chat')}
          </button>
        </div>
      </div>
    );
  }
}
export default EndChatConfirmPopup;
