import React from 'react';

export class MessageFooter extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-message-footer">
        <a href="#" className="dpdesignportal-message-footer-assets-link"><i className="fa fa-copy"></i> Chat Assets (4)</a>
        <span className="dpdesignportal-message-footer-timer">5m ago</span>
      </div>
    );
  }
}
