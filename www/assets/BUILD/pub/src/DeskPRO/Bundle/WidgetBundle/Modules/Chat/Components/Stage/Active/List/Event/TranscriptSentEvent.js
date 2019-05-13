import React from 'react';

export class TranscriptSentEvent extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-transcript-sent">
        <div className="left">
          <i className="fas fa-check" />
          <span>We have emailed you a transcript</span>
        </div>

        <div className="right">
          <a href="#">
            <i className="fas fa-print" /><span>Print</span>
          </a>
        </div>
      </div>
    );
  }
}
