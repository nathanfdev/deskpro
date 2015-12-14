import React, { PropTypes } from 'react';
import { AttachmentLink } from './Assets/AttachmentLink';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';

export class MessageContent extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;
    const content = message.get('is_html') ? replaceSmileCodes(message.get('content')) : message.get('content');

    return (
      <div>
        {message.get('is_html')
          ? <p dangerouslySetInnerHTML={{__html: content}} />
          : <p>{content}</p>
        }

        {false /* disabled */ &&
          <ul>
            <li>
              <AttachmentLink />
            </li>
          </ul>
        }
      </div>
    );
  }
}
