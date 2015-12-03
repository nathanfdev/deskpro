import React, { PropTypes } from 'react';
import { Message } from './Message';
import { MessageAvatar } from './MessageAvatar';
import { MessageFooter } from './MessageFooter';
import { AttachmentLink } from './Assets/AttachmentLink';

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
          <p>{message.get('content')}</p>

          <ul>
            <li>
              <AttachmentLink />
            </li>
          </ul>
        </div>

        <MessageFooter {...this.props} />
      </Message>
    );
  }
}
