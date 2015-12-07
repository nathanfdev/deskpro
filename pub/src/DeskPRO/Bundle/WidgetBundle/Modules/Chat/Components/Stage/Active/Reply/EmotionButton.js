import React, { PropTypes } from 'react';
import { EmotionsPopup } from './EmotionsPopup';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import classNames from 'classnames';

export class EmotionButton extends React.Component {

  static propTypes = {
    getEditor: PropTypes.func,
    onSelect: PropTypes.func
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

  onSelectEmotion = num => {
    const className = classNames('emoticon', 'sprite', `sprite-emoticon-${num}`);
    const medium = this.props.getEditor();

    if (medium.getFocusedElement()) {
      medium.restoreSelection();
    } else {
      medium.trigger('focus');
    }

    medium.pasteHTML(`text<span class="${className}"></span>`);

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
          positionAt="center top-15"
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
