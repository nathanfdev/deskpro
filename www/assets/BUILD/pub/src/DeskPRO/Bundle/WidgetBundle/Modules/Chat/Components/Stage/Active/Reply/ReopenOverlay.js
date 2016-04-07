import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ReopenOverlay extends React.Component {

  static propTypes = {
    locked: PropTypes.bool,
    onReopen: PropTypes.func
  };

  onReopen = event => {
    event.preventDefault();
    this.props.onReopen();
  };

  render() {
    return (
      <div className="dpdesignportal-chat-form-disabled">
        <a href="#" className={classNames('dpdesignportal-button', {'locked': this.props.locked})} onClick={this.onReopen}>
          <i className="fa fa-commenting-o"></i> {portalPhrases.get('portal.chat.reopen_chat')}
        </a>
      </div>
    );
  }
}
