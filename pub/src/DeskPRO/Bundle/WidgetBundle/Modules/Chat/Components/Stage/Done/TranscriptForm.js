import React from 'react';

export class TranscriptForm extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-transcript-request">
        <div className="dpdesignportal-transcript-request-header">
          <span>Need a transcript of your chat?</span>
          <a href="#" className="dpdesignportal-button">Send transcript</a>
          <a href="#" className="dpdesignportal-button"><i className="fa fa-print"></i></a>
        </div>

        <div className="dpdesignportal-transcript-request-form-container">
          <form className="dpdesignportal-form">
            <label className="inline-form-item">
              <span className="dpdesignportal-form-item-label-title">Your name:</span>
              <input type="text" />
            </label>

            <label className="inline-form-item">
              <span className="dpdesignportal-form-item-label-title inline-title">Your email:</span>
              <input type="text" />
            </label>

            <div className="label button-label">
              <input type="submit" value="Send me a transcript" className="dpdesignportal-button dpdesignportal-button-wide" />
            </div>
          </form>
        </div>
      </div>
    );
  }
}
