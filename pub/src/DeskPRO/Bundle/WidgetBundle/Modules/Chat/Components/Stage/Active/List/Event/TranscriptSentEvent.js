import React from 'react';

export class TranscriptSentEvent extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-transcript-sent">
        <div className="left">
          <i className="fa fa-check"></i>
          <span>We have emailed you a transcript</span>
        </div>

        <div className="right">
          <a href="#">
            <i className="fa fa-print"></i><span>Print</span>
          </a>
        </div>
      </div>
    );
  }
}
