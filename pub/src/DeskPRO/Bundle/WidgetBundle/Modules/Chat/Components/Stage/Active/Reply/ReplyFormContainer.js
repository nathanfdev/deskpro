import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ReplyForm } from './ReplyForm';
import { ReopenChatContainer } from '../ReopenChatContainer';
import { addAttachment } from '../../../../Actions/chatActions';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import { sendChatMessage, removeAttachment } from '../../../../Actions/chatActions';
import {
  chatIdSelector,
  agentNameSelector,
  attachmentsSelector,
  attachedImagesSelector,
  attachedImagesCountSelector,
  attachedFilesSelector
} from '../../../../Selectors/chat';

@connect(state => ({
  chatId: chatIdSelector(state),
  agentName: agentNameSelector(state),
  attachments: attachmentsSelector(state),
  attachedImages: attachedImagesSelector(state),
  attachedImagesCount: attachedImagesCountSelector(state),
  attachedFiles: attachedFilesSelector(state)
}))
export class ReplyFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    chatId: PropTypes.number,
    attachments: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      uploading: []
    };
  }

  onSendMessage = message => {
    const { dispatch, chatId, attachments } = this.props;
    const data = {
      message: replaceSmileCodes(message, true),
      attachments: attachments.map(attachment => attachment.get('blob_auth_id'))
    };

    dispatch(sendChatMessage(chatId, data));
  };

  onUploadedStarted = (event, data) => {
    this.setState({
      uploading: this.state.uploading.concat(data.files)
    });
  };

  onUploadedSuccess = (event, response) => {
    const attachments = response.result && response.result.data || [];
    attachments.forEach(attachment => this.props.dispatch(addAttachment(attachment)));

    this.removeFilesFromQueue(response);
  };

  onUploadedFail = (event, response) => {
    this.removeFilesFromQueue(response);
  };

  onRemoveFile = attachment => {
    this.props.dispatch(removeAttachment(attachment));
  };

  removeFilesFromQueue(response) {
    response.files.forEach(file => setTimeout(() => {
      const files = this.state.uploading;
      const index = files.indexOf(file);

      if (index !== -1) {
        files.splice(index, 1);
      }

      this.setState({
        uploading: files
      });
    }, 0));
  }

  render() {
    return (
      <ReopenChatContainer>
        <ReplyForm onSendMessage={this.onSendMessage}
                   onUploadedStarted={this.onUploadedStarted}
                   onUploadedSuccess={this.onUploadedSuccess}
                   onUploadedFail={this.onUploadedFail}
                   onRemoveFile={this.onRemoveFile}
                   uploadingFiles={this.state.uploading} {...this.props} />

      </ReopenChatContainer>
    );
  }
}
