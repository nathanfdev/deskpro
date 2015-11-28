import React from 'react';
import { Message } from './Message';
import { MessageFooter } from './MessageFooter';

export class UserMessage extends React.Component {

  render() {
    return (
      <Message type="user">
        <div className="dpdesignportal-message-avatar"></div>
        <div className="dpdesignportal-message-content">
          <p>I have already written a view to show the current order status of every order that was touched today. I based it off of the view that exists in the system that is based off of the audit table.</p>

          <ul>
            <li>
              <a href="#" className="dpdesignportal-message-asset attachement-link">
                <span className="dpdesignportal-message-asset-icon"><i className="fa fa-link"></i></span>
                <span className="dpdesignportal-message-asset-cta">Can i buy a part-time or "light" agent license?</span>
              </a>
            </li>
          </ul>
        </div>

        <MessageFooter />
      </Message>
    );
  }
}
