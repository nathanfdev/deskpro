import React, { PropTypes } from 'react';
import { EndChatContainer } from '../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import EmotionButton from 'DeskPRO/Component/Rte/EmotionButton';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';
import RteInput from 'DeskPRO/Component/Rte/RteInput';
import ScrollArea from 'react-scrollbar';
import { AttachedFile } from './AttachedFile';

export class ReplyForm extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool,
    canReopen: PropTypes.bool,
    onSendMessage: PropTypes.func,
    onReopen: PropTypes.func,
    agentName: PropTypes.string
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

  onSubmit = event => {
    event.preventDefault();

    this.props.onSendMessage(replaceSmileCodes(this.state.message, true));
    this.setState({
      message: ''
    });
  };

  onReopen = event => {
    event.preventDefault();
    this.props.onReopen();
  };

  onRemoveFile = fileId => {
    console.log('onRemoveFile ' + fileId);
  };

  render() {
    const { agentName, isEnded, canReopen } = this.props;
    const currentFrame = parent.window.widget_iframe;

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
            <ScrollArea vertical>
              <RteInput
                inline
                ref="editor"
                value={this.state.message}
                onChange={this.onChangeMessage}
                onSubmit={this.onSubmit}
                className="textarea"
                options={{
                  contentWindow: currentFrame.window,
                  ownerDocument: currentFrame.document,
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

            <AttachedFile fileId={1} name="file_name_lorem_ipsum.pdf" onRemove={this.onRemoveFile} />
            <AttachedFile fileId={2} name="file_name_lorem_ipsum.pdf" onRemove={this.onRemoveFile} />
            <AttachedFile fileId={3} name="file_name_lorem_ipsum.pdf" onRemove={this.onRemoveFile} />
          </div>

          <button>
            <i className="fa fa-angle-double-right"></i>
          </button>
        </form>

        <div className="dpdesignportal-chat-form-button-row">
          <div className="dpdesignportal-chat-form-button-row-main">
            <a href="#" onClick={this.onUploadFile}>
              <i className="fa fa-upload"></i> Upload file
              <input type="file" className="file" name="file-upload" />
            </a>
            <a href="#" onClick={this.onScreenShare}>
              <i className="fa fa-camera"></i> Screen Share
            </a>
            <EmotionButton
              buttonClassName="img"
              context={[parent.document, parent.window.widget_iframe.document]}
              getEditor={() => this.refs.editor.getMediumEditor()}
              popupPositionAt="center top-15"
              popupPositionMy="center bottom"
            />
          </div>

          <EndChatContainer>
            <EndChatButton />
          </EndChatContainer>
        </div>
      </div>
    );
  }
}
