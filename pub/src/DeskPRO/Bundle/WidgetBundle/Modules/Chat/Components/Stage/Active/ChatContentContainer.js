import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { windowResize } from '../../../../Application/Actions/dpWindowActions';
import {
  isEndedSelector,
  messagesSelector,
  uploadingFilesSelector,
  attachmentsSelector
} from '../../../Selectors/chat';

@connect(state => ({
  isEnded: isEndedSelector(state),
  messages: messagesSelector(state),
  uploading: uploadingFilesSelector(state),
  attachments: attachmentsSelector(state)
}))
export class ChatContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.any
  };

  componentWillReceiveProps() {
    this.props.dispatch(windowResize());
  }

  render() {
    return (
      <div className="dpdesignportal-chat-footer">
        {this.props.children}
      </div>
    );
  }
}
