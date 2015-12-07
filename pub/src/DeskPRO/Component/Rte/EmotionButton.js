import React, { PropTypes } from 'react';
import { EmotionsPopup } from './EmotionsPopup';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Simple from 'DeskPRO/Component/Positioned/Simple';

export default class EmotionButton extends React.Component {

  static propTypes = {
    getEditor: PropTypes.func,
    onSelect: PropTypes.func,
    context: PropTypes.object,
    className: PropTypes.string,
    buttonClassName: PropTypes.string

  };

  constructor(props) {
    super(props);
    this.state = {
      emotionsPopup: false
    };
  }

  onSelectEmoticon = event => {
    event.preventDefault();

    const medium = this.props.getEditor();
    medium.saveSelection();
    
    this.setState({
      emotionsPopup: true
    });
  };

  onCloseEmotionsPopup = () => {
    const medium = this.props.getEditor();
    medium.restoreSelection();

    this.setState({
      emotionsPopup: false
    });
  };

  onSelectEmotion = text => {
    const medium = this.props.getEditor();

    medium.restoreSelection();
    medium.pasteHTML(` ${text}`);

    this.onCloseEmotionsPopup();
  };

  render() {
    return (
      <span className={this.props.className}>
        <a href="#"
           className="dpdesignportal-chat-form-button-row-emoticons"
           title="Chat Emoticons"
           onClick={this.onSelectEmoticon}>

          <span className={this.props.buttonClassName} ref="emotionsButton" />
        </a>

        <Simple
          isOpen={this.state.emotionsPopup}
          positionTarget={this.refs.emotionsButton}
          positionAt="center top-15"
          positionMy="center bottom"
          zIndex={1000}>

          <ClickOut
            onClickOut={this.onCloseEmotionsPopup}
            context={this.props.context}>

            <EmotionsPopup onClick={this.onSelectEmotion} />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
