import React, { PropTypes } from 'react';
import { EndChatContainer } from '../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import { EmotionButton } from 'DeskPRO/Component/Rte/EmotionButton';
import { RteEditor } from 'DeskPRO/Component/Rte/RteEditor';
import { UploadingFilesContainer } from './Upload/Uploading/UploadingFilesContainer';
import { UploadingFiles } from './Upload/Uploading/UploadingFiles';
import { AttachmentContainer } from './Upload/Attachment/AttachmentContainer';
import { AttachedFiles } from './Upload/Attachment/File/AttachedFiles';
import { AttachedImages } from './Upload/Attachment/Image/AttachedImages';
import { DropZoneContainer } from './Upload/DropZone/DropZoneContainer';
import { PasteCatcher } from 'DeskPRO/Component/Uploader/PasteCatcher';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import { DropZoneOverlay } from './Upload/DropZone/DropZoneOverlay';
import { ReopenOverlay } from './ReopenOverlay';
import ScrollArea from 'react-scrollbar-iframe';

export class ReplyForm extends React.Component {

  static propTypes = {
    agentName:           PropTypes.string,
    attachedImagesCount: PropTypes.number,
    isEnded:             PropTypes.bool,
    canReopen:           PropTypes.bool,
    onUserTyping:        PropTypes.func,
    onSendMessage:       PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      message: ''
    };
  }

  onChangeMessage = value => {
    this.setState({
      message: value
    });

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

  onPasteImage = file => {
    this.refs.dropZone.pushFileToQueue(file);
  };

  onScreenShare = event => {
    event.preventDefault();
    console.log('onScreenShare');
  };

  onSubmit = event => {
    event.preventDefault();

    this.props.onSendMessage(this.state.message);
    this.setState({
      message: ''
    });
  };

  renderRte() {
    return (
      <ScrollArea vertical>
        <RteEditor
          inline
          ref="editor"
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
            placeholder:   {
              text: `Type your message to ${this.props.agentName}`
            },
            toolbar: {
              buttons:                ['bold', 'italic', 'underline'],
              updateOnEmptySelection: true
            }
          }} />
      </ScrollArea>
    );
  }

  render() {
    const { attachedImagesCount, isEnded, canReopen } = this.props;

    if (isEnded && !canReopen) {
      return null;
    }

    return (
      <div className="dpdesignportal-chat-form">
        {isEnded && <ReopenOverlay {...this.props} />}

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

          <button>
            <i className="fa fa-angle-double-right" />
          </button>
        </form>

        <div className="dpdesignportal-chat-form-button-row">
          <div className="dpdesignportal-chat-form-button-row-main">
            <span className="dpdesignportal-chat-form-button">
              <i className="fa fa-upload" /> Upload file
              <input ref="fileUpload" className="file" type="file" name="files[]" multiple="multiple" />
            </span>

            {false /* disabled for now */ &&
              <a href="#" className="dpdesignportal-chat-form-button" onClick={this.onScreenShare}>
                <i className="fa fa-camera" /> Screen Share
              </a>
            }

            <EmotionButton
              buttonClassName="img"
              context={[parent.document, window.widgetFrame.document]}
              getEditor={() => this.refs.editor}
              popupPositionAt="center top-15"
              popupPositionMy="center bottom" />
          </div>

          <EndChatContainer>
            <EndChatButton />
          </EndChatContainer>
        </div>

        <DropZoneContainer>
          <DropZone ref="dropZone"
                    getExternalInput={() => this.refs.fileUpload}
                    uploadUrl={`${window.DP_HELPDESK_URL}portal/api/blobs/temp`}>

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
