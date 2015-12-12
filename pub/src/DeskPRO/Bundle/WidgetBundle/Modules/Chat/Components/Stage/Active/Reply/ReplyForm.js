import React, { PropTypes } from 'react';
import { EndChatContainer } from '../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import EmotionButton from 'DeskPRO/Component/Rte/EmotionButton';
import RteInput from 'DeskPRO/Component/Rte/RteInput';
import ScrollArea from 'react-scrollbar';
import { AttachedFiles } from './Upload/Attached/File/AttachedFiles';
import { DropZone } from './Upload/DropZone/DropZone';

export class ReplyForm extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool,
    canReopen: PropTypes.bool,
    onSendMessage: PropTypes.func,
    onUploadedFile: PropTypes.func,
    onRemoveFile: PropTypes.func,
    onReopen: PropTypes.func,
    agentName: PropTypes.string,
    attachments: PropTypes.object
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
  };

  onScreenShare = event => {
    event.preventDefault();
    console.log('onScreenShare');
  };

  onReopen = event => {
    event.preventDefault();
    this.props.onReopen();
  };

  onSubmit = event => {
    event.preventDefault();

    this.props.onSendMessage(this.state.message);
    this.setState({
      message: ''
    });
  };

  render() {
    const { agentName, isEnded, canReopen } = this.props;
    const { attachments, onUploadedFile, onRemoveFile } = this.props;

    if (isEnded && !canReopen) {
      return null;
    }

    return (
      <div className="dpdesignportal-chat-form">
        {isEnded &&
          <div className="dpdesignportal-chat-form-disabled">
            <a href="#" className="dpdesignportal-button" onClick={this.onReopen}>
              <i className="fa fa-commenting-o"></i> Reopen this chat
            </a>
          </div>
        }

        <form onSubmit={this.onSubmit}>
          <div className="message-container message-container-with-attached-images">
            <div className="dpdesignportal-chat-form-attached-image">
              <div className="dpdesignportal-chat-form-attached-image-count">
                12 <i className="fa fa-angle-double-right"></i>
              </div>
              <div className="dpdesignportal-chat-form-attached-image-thumb" />
            </div>
            <div className="textarea-container">
              <ScrollArea vertical>
                <RteInput
                  inline
                  ref="editor"
                  value={this.state.message}
                  onChange={this.onChangeMessage}
                  onSubmit={this.onSubmit}
                  className="textarea"
                  options={{
                    contentWindow: window.widgetFrame.window,
                    ownerDocument: window.widgetFrame.document,
                    autoLink: true,
                    imageDragging: true,
                    placeholder: {
                      text: `Type your message to ${agentName}`
                    },
                    toolbar: {
                      buttons: ['bold', 'italic', 'underline'],
                      updateOnEmptySelection: true
                    },
                    paste: {
                      forcePlainText: false,
                      cleanPastedHTML: false,
                      cleanAttrs: ['style', 'dir']
                    }
                  }}
                />
              </ScrollArea>
            </div>
            <AttachedFiles attachments={attachments} onRemoveFile={onRemoveFile} />
          </div>

          <button>
            <i className="fa fa-angle-double-right"></i>
          </button>
        </form>

        <div className="dpdesignportal-chat-form-button-row">
          <div className="dpdesignportal-chat-form-button-row-main">
            <span className="dpdesignportal-chat-form-button">
              <i className="fa fa-upload"></i> Upload file
              <input ref="fileUpload" className="file" type="file" name="files[]" multiple />
            </span>
            <a href="#" className="dpdesignportal-chat-form-button" onClick={this.onScreenShare}>
              <i className="fa fa-camera"></i> Screen Share
            </a>
            <EmotionButton
              buttonClassName="img"
              context={[parent.document, window.widgetFrame.document]}
              getEditor={() => this.refs.editor.getMediumEditor()}
              popupPositionAt="center top-15"
              popupPositionMy="center bottom"
            />
          </div>

          <EndChatContainer>
            <EndChatButton />
          </EndChatContainer>
        </div>

        <DropZone getExternalInput={() => this.refs.fileUpload}
                  uploadUrl={window.DP_HELPDESK_URL + 'portal/api/blobs/temp'}
                  context={[window.widgetFrame.document, parent.window.document]}
                  onSuccess={onUploadedFile} />
      </div>
    );
  }
}
