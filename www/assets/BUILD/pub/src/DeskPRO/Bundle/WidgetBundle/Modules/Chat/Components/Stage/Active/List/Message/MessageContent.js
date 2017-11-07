import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import { AttachmentLink } from './Assets/AttachmentLink';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import $ from 'jquery';

export class MessageContent extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  componentDidMount() {
    const contentNode = ReactDOM.findDOMNode(this);
    $('a', contentNode).each((i, linkNode) => {
      $(linkNode).attr('target', '_blank');
    });
  }

  render() {
    const { message } = this.props;
    const content = message.get('is_html') ? replaceSmileCodes(message.get('content')) : message.get('content');

    return (
      <div>
        {message.get('is_html')
          ? <p dangerouslySetInnerHTML={{ __html: content }} />
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
