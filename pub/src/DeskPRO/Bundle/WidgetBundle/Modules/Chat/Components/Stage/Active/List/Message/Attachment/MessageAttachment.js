import React, { PropTypes } from 'react';
import { MessageImage } from './MessageImage';
import { MessageFile } from './MessageFile';

export class MessageAttachment extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const props = this.props;
    const attachment = props.message.get('metadata').get('blob');
    const newProps = {...props, attachment};

    return attachment.get('is_image') ? <MessageImage {...newProps} /> : <MessageFile {...newProps} />;
  }
}
