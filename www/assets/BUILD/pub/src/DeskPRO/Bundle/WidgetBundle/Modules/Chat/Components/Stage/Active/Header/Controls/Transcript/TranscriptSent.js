import React, { PropTypes } from 'react';

export class TranscriptSent extends React.Component {

  static propTypes = {
    email: PropTypes.string
  };

  render() {
    return (
      <div className="dpdesignportal-popover-request-transcript-sent-message">
        A transcript has already been sent to <b>{this.props.email}</b>
      </div>
    );
  }
}
