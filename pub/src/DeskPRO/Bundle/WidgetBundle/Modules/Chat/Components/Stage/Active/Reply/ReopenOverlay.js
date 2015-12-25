import React, { PropTypes } from 'react';
import classNames from 'classnames';

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
          <i className="fa fa-commenting-o"></i> Reopen this chat
        </a>
      </div>
    );
  }
}
