import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { windowResize } from '../../../../Application/Actions/dpWindowActions';
import {
  chatLoadedSelector,
  isEndedSelector,
  messagesSelector,
  uploadingFilesSelector,
  attachmentsSelector,
  feedbackStageSelector
} from '../../../Selectors/chat';

// Define chat selectors to force dispatch to re render the widget content
@connect(state => ({
  chatLoaded: chatLoadedSelector(state),
  isEnded: isEndedSelector(state),
  messages: messagesSelector(state),
  uploading: uploadingFilesSelector(state),
  attachments: attachmentsSelector(state),
  feedbackStage: feedbackStageSelector(state)
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
