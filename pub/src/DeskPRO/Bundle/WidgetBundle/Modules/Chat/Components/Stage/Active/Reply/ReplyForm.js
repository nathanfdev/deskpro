import React, { PropTypes } from 'react';
import { EndChatContainer } from '../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import EmotionButton from 'DeskPRO/Component/Rte/RteInput';
import RteInput from 'DeskPRO/Component/Rte/RteInput';
import ScrollArea from 'react-scrollbar';

export class ReplyForm extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool,
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

  onUploadFile = event => {
    event.preventDefault();
    console.log('onUploadFile');
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

  onReopen = event => {
    event.preventDefault();
    this.props.onReopen();
  };

  render() {
    const { agentName, isEnded } = this.props;
    const currentFrame = parent.window.widget_iframe;

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
          <div className="message-container">
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
          </div>

          <button>
            <i className="fa fa-angle-double-right"></i>
          </button>
        </form>

        <div className="dpdesignportal-chat-form-button-row">
          <div className="dpdesignportal-chat-form-button-row-main">
            <a href="#" onClick={this.onUploadFile}>
              <i className="fa fa-upload"></i> Upload file
            </a>
            <a href="#" onClick={this.onScreenShare}>
              <i className="fa fa-camera"></i> Screen Share
            </a>
            <EmotionButton getEditor={() => this.refs.editor.getMediumEditor()} />
          </div>

          <EndChatContainer>
            <EndChatButton />
          </EndChatContainer>
        </div>
      </div>
    );
  }
}
