import React from 'react';

export class TranscriptForm extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-popover dpdesignportal-popover-request-transcript">
        <div className="dpdesignportal-popover-close"><i className="fa fa-times"></i></div>
        <h1>Need a transcript of this chat?</h1>
        <p className="grey">Enter your name &amp; email below and we'll email it to you.</p>

        <div className="popover-form">
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
              <input type="submit" value="Send me a transcript" className="dpdesignportal-button" />
            </div>

          </form>
        </div>
      </div>
    );
  }
}
