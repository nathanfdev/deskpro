import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ReopenOverlay extends React.Component {

  static propTypes = {
    locked:         PropTypes.bool,
    onReopen:       PropTypes.func,
    lostConnection: PropTypes.bool
  };

  onReopen = event => {
    event.preventDefault();
    this.props.onReopen();
  };

  render() {
    const { locked, lostConnection } = this.props;

    return (
      <div className="dpdesignportal-chat-form-disabled">
        {lostConnection
          ? null
          : <button className={classNames('dpdesignportal-button', { locked })} onClick={this.onReopen}>
            <i className="fa fa-commenting-o" /> {portalPhrases.get('portal.chat.reopen_chat')}
          </button>
        }
      </div>
    );
  }
}
export default ReopenOverlay;
