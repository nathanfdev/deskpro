import React, { PropTypes } from 'react';
import { Message } from './Message';
import { MessageAvatar } from './MessageAvatar';
import { MessageFooter } from './MessageFooter';

export class UserMessage extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;

    return (
      <Message type="user">
        <MessageAvatar />
        <div className="dpdesignportal-message-content">
          <p>{message.get('message')}</p>

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
