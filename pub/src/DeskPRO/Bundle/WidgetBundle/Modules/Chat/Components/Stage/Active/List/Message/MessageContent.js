import React, { PropTypes } from 'react';
import { AttachmentLink } from './Assets/AttachmentLink';

export class MessageContent extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;

    return (
      <div className="dpdesignportal-message-content">
        <p>{message.get('content')}</p>

        <ul>
          <li>
            <AttachmentLink />
          </li>
        </ul>
      </div>
    );
  }
}
