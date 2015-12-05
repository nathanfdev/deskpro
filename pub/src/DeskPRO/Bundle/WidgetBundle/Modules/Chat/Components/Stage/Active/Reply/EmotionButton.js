import React, { PropTypes } from 'react';
import { EmotionsPopup } from './EmotionsPopup';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Simple from 'DeskPRO/Component/Positioned/Simple';

export class EmotionButton extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      emotionsPopup: false
    };
  }

  onSelectEmoticon = event => {
    event.preventDefault();
    this.setState({
      emotionsPopup: true
    });
  };

  onCloseEmotionsPopup = () => {
    this.setState({
      emotionsPopup: false
    });
  };

  onSelectEmotion = emotion => {
    console.log('onSelectEmotion', emotion);
    this.onCloseEmotionsPopup();
  };

  render() {
    return (
      <span>
        <a href="#"
           className="dpdesignportal-chat-form-button-row-emoticons"
           title="Chat Emoticons"
           onClick={this.onSelectEmoticon}>

          <span className="img" ref="emotionsButton" />
        </a>

        <Simple
          isOpen={this.state.emotionsPopup}
          positionTarget={this.refs.emotionsButton}
          positionAt="right top"
          positionMy="center bottom"
          zIndex={1000}>

          <ClickOut
            onClickOut={this.onCloseEmotionsPopup}
            context={[parent.document, parent.window.widget_iframe.document]}>

            <EmotionsPopup onClick={this.onSelectEmotion} />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
