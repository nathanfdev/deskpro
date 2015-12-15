import React, { PropTypes } from 'react';

export class ReopenOverlay extends React.Component {

  static propTypes = {
    onReopen: PropTypes.func
  };

  onReopen = event => {
    event.preventDefault();
    this.props.onReopen();
  };

  render() {
    return (
      <div className="dpdesignportal-chat-form-disabled">
        <a href="#" className="dpdesignportal-button" onClick={this.onReopen}>
          <i className="fa fa-commenting-o"></i> Reopen this chat
        </a>
      </div>
    );
  }
}
