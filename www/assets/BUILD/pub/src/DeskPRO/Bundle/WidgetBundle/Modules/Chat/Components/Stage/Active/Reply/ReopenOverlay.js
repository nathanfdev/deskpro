import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class ReopenOverlay extends React.Component {

  static propTypes = {
    locked:   PropTypes.bool,
    onReopen: PropTypes.func
  };

  onReopen = event => {
    event.preventDefault();
    this.props.onReopen();
  };

  render() {
    const { locked } = this.props;

    return (
      <div className="dpdesignportal-chat-form-disabled">
        <a href="#" className={classNames('dpdesignportal-button', { locked })} onClick={this.onReopen}>
          <i className="fa fa-commenting-o" /> Reopen this chat
        </a>
      </div>
    );
  }
}
