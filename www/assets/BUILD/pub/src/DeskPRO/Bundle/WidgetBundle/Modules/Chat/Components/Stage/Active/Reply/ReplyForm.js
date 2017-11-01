import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';
import { EmotionButton } from 'DeskPRO/Component/Rte/EmotionButton';
import RteEditor from 'DeskPRO/Component/Rte/RteEditor';
import { PasteCatcher } from 'DeskPRO/Component/Uploader/PasteCatcher';
import { UploadButton } from 'DeskPRO/Component/Uploader/UploadButton';
import DropZone from 'DeskPRO/Component/Uploader/DropZone';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { UploadingFilesContainer } from './Upload/Uploading/UploadingFilesContainer';
import { UploadingFiles } from './Upload/Uploading/UploadingFiles';
import { AttachmentContainer } from './Upload/Attachment/AttachmentContainer';
import { AttachedFiles } from './Upload/Attachment/File/AttachedFiles';
import { AttachedImages } from './Upload/Attachment/Image/AttachedImages';
import { DropZoneContainer } from './Upload/DropZone/DropZoneContainer';
import { DropZoneOverlay } from './Upload/DropZone/DropZoneOverlay';
import { ReopenOverlay } from './ReopenOverlay';
import { EndChatContainer } from '../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';

export class ReplyForm extends React.Component {

  static propTypes = {
    agentName:           PropTypes.string,
    attachedImagesCount: PropTypes.number,
    isEnded:             PropTypes.bool,
    lostConnection:      PropTypes.bool,
    canReopen:           PropTypes.bool,
    onUserTyping:        PropTypes.func,
    onSendMessage:       PropTypes.func,
    primaryColor:        PropTypes.string
  };

  static getUploadUrl() {
    return `${window.DP_HELPDESK_URL}portal/api/blobs/temp`;
  }

  constructor(props) {
    super(props);

    let message = '';
    if (storageAvailable('localStorage') && localStorage.getItem('dpWidget.chat.partial')) {
      message = localStorage.getItem('dpWidget.chat.partial');
    }

    this.state = { message };
  }

  shouldComponentUpdate(props, state) {
    const { agentName, attachedImagesCount, isEnded, lostConnection, canReopen } = this.props;
    return props.agentName !== agentName
      || props.attachedImagesCount !== attachedImagesCount
      || props.isEnded !== isEnded
      || props.lostConnection !== lostConnection
      || props.canReopen !== canReopen
      || state.message === '';
  }

  onChangeMessage = (message) => {
    this.setState({ message });

    if (!this.timeout) {
      const onUserTyping = () => {
        if (this.state.message !== '<p><br></p>') {
          this.props.onUserTyping(this.state.message);
        }

        this.timeout = null;
      };

      this.timeout = setTimeout(onUserTyping, 1000);
    }
  };

  onPasteImage = (file) => {
    this.uploadButton.pushFileToQueue(file);
  };

  onScreenShare = (event) => {
    event.preventDefault();
    console.log('onScreenShare');
  };

  onSubmit = (event) => {
    event.preventDefault();

    this.props.onSendMessage(this.state.message);
    this.setState({ message: '' });
  };

  renderRte() {
    return (
      <ScrollArea vertical>
        <RteEditor
          inline
          ref={(c) => { this.editor = c; }}
          value={this.state.message}
          onChange={this.onChangeMessage}
          onSubmit={this.onSubmit}
          className="textarea"
          onPasteImage={this.onPasteImage}
          options={{
            contentWindow: window.widgetFrame.window,
            ownerDocument: window.widgetFrame.document,
            autoLink:      true,
            imageDragging: true,

            placeholder: {
              // replaced this.props.agentName to default 'Agent'
              // because the placeholder text is not updated on re-assign agent properly (e.g. on change this.props.agentName value)
              text: portalPhrases.get('portal.chat.message_type', { '{agentName}': 'Agent' })
            },
            toolbar: {
              buttons:                ['bold', 'italic', 'underline'],
              updateOnEmptySelection: true
            }
          }}
        />
      </ScrollArea>
    );
  }

  render() {
    const { attachedImagesCount, isEnded, canReopen, lostConnection, primaryColor } = this.props;
    const sendButtonStyles = {};
    if (primaryColor) {
      sendButtonStyles.backgroundColor = primaryColor;
    }

    if (isEnded && !canReopen) {
      return null;
    }

    return (
      <div className="dpdesignportal-chat-form">
        {(isEnded || lostConnection) && <ReopenOverlay {...this.props} />}

        <form onSubmit={this.onSubmit}>
          <div className="message-container message-container-with-attached-images">
            {attachedImagesCount
              ? <div>
                <AttachmentContainer>
                  <AttachedImages />
                </AttachmentContainer>

                <div className="textarea-container">
                  {this.renderRte()}
                </div>
              </div>
              : this.renderRte()
            }

            <UploadingFilesContainer>
              <UploadingFiles />
            </UploadingFilesContainer>

            <AttachmentContainer>
              <AttachedFiles />
            </AttachmentContainer>
          </div>

          <button className="send" style={sendButtonStyles}>
            <i className="fa fa-angle-double-right" />
          </button>
        </form>

        <div className="dpdesignportal-chat-form-button-row">
          <div className="dpdesignportal-chat-form-button-row-main">
            <span className="dpdesignportal-chat-form-button">
              <i className="fa fa-upload" /> {portalPhrases.get('portal.chat.upload_file')}
              <DropZoneContainer>
                <UploadButton
                  ref={(c) => { this.uploadButton = c; }}
                  multiple
                  className="file"
                  name="files"
                  uploadUrl={ReplyForm.getUploadUrl()}
                />
              </DropZoneContainer>
            </span>

            {false /* disabled for now */ &&
              <button className="dpdesignportal-chat-form-button" onClick={this.onScreenShare}>
                <i className="fa fa-camera" /> {portalPhrases.get('portal.chat.screen_share')}
              </button>
            }

            <EmotionButton
              buttonClassName="img"
              context={[parent.document, window.widgetFrame.document]}
              getEditor={() => this.editor}
              popupPositionAt="center top-15"
              popupPositionMy="center bottom"
            />
          </div>

          <EndChatContainer>
            <EndChatButton />
          </EndChatContainer>
        </div>

        <input
          ref={(c) => { this.fileUpload = c; }}
          className="hidden"
          type="file"
          name="files[]"
          multiple="multiple"
        />
        <DropZoneContainer instant>
          <DropZone
            getExternalInput={() => this.fileUpload}
            uploadUrl={ReplyForm.getUploadUrl()}
          >
            <DragOverlayListener context={[parent.document, window.widgetFrame.document]}>
              <DropZoneOverlay />
            </DragOverlayListener>
          </DropZone>
        </DropZoneContainer>

        <PasteCatcher context={window.widgetFrame.document} onPasteImage={this.onPasteImage} />
      </div>
    );
  }
}
export default ReplyForm;
