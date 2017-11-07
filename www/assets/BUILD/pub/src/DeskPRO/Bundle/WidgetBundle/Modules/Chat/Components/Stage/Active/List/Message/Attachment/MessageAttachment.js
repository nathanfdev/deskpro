import PropTypes from 'prop-types';
import React from 'react';
import { MessageImage } from './MessageImage';
import { MessageFile } from './MessageFile';
import Immutable from 'immutable';

export class MessageAttachment extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const props = this.props;
    const { message = Immutable.fromJS({}) } = this.props;

    const metadata = message.get('metadata') || Immutable.fromJS({});
    const attachment = metadata.get('blob') || Immutable.fromJS({});
    const newProps = { ...props, attachment };

    return attachment.get('is_image') ? <MessageImage {...newProps} /> : <MessageFile {...newProps} />;
  }
}
