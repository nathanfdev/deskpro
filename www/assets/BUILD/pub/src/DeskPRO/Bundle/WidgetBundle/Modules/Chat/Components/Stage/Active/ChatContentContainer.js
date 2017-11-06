import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { widgetResize } from '../../../../Application/Actions/dpWindowActions';
import {
  chatLoadedSelector,
  isEndedSelector,
  messagesSelector,
  uploadingFilesSelector,
  attachmentsSelector,
  feedbackStageSelector,
  canReopenSelector
} from '../../../Selectors/chat';

// Define chat selectors to force dispatch to re render the widget content
@connect(state => ({
  chatLoaded:    chatLoadedSelector(state),
  isEnded:       isEndedSelector(state),
  messages:      messagesSelector(state),
  uploading:     uploadingFilesSelector(state),
  attachments:   attachmentsSelector(state),
  feedbackStage: feedbackStageSelector(state),
  canReopen:     canReopenSelector(state)
}))
export class ChatContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node
  };

  componentWillReceiveProps(newProps) {
    if (this.props.children !== newProps.children) {
      return;
    }

    this.props.dispatch(widgetResize());
  }

  render() {
    return (
      <div className="dpdesignportal-content">
        {this.props.children}
      </div>
    );
  }
}
export default ChatContentContainer;
