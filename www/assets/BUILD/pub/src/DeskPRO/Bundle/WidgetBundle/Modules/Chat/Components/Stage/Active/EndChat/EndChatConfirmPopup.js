import React, { PropTypes } from 'react';
import classNames from 'classnames';

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
        <h1>Are you sure you want to end this chat?</h1>

        <div className="popover-buttons">
          <a href="#" className={classNames('dpdesignportal-button', {'locked': locked})} onClick={onConfirm}>
            <i className="fa fa-power-off" /> End Chat
          </a>
          <a href="#" className="dpdesignportal-button grey" onClick={onCancel}>
            <i className="fa fa-reply" /> Cancel and return to chat
          </a>
        </div>
      </div>
    );
  }
}
