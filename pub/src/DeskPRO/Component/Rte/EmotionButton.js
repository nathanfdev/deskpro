import React, { PropTypes } from 'react';
import { EmotionsPopup } from './EmotionsPopup';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import * as Emotions from './Emotions';
import jQuery from 'jquery';

export default class EmotionButton extends React.Component {

  static propTypes = {
    getEditor: PropTypes.func,
    onSelect: PropTypes.func,
    context: PropTypes.any,
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

  onSelectEmotion = code => {
    const medium = this.props.getEditor();

    medium.restoreSelection();

    const contentWindow = medium.options.contentWindow;
    const ownerDocument = medium.options.ownerDocument;

    if (!medium.checkSelection().selectionState) {
      medium.trigger('initialFocus');
    }

    const container = medium.getSelectedParentElement();
    const node = jQuery.parseHTML(Emotions.createEmotionImage(code))[0];
    const selection = contentWindow.getSelection();
    const textNode = ownerDocument.createTextNode(' ');
    const lastNode = ownerDocument.createTextNode(' ');

    container.appendChild(textNode);
    container.appendChild(node);
    container.appendChild(lastNode);
    selection.removeAllRanges();

    const range = ownerDocument.createRange();
    range.setStartAfter(lastNode);
    range.collapse(true);
    selection.addRange(range);

    medium.saveSelection();
    medium.pasteHTML('');

    this.onCloseEmotionsPopup();
  };

  render() {
    const { className, buttonClassName, context } = this.props;

    return (
      <span className={className}>
        <a href="#"
           className="dpdesignportal-chat-form-button-row-emoticons"
           title="Chat Emoticons"
           onClick={this.onSelectEmoticon}>

          <span className={buttonClassName} ref="emotionsButton" />
        </a>

        <Simple
          isOpen={this.state.emotionsPopup}
          positionTarget={this.refs.emotionsButton}
          positionAt="center top-15"
          positionMy="center bottom"
          zIndex={1000}>

          <ClickOut
            onClickOut={this.onCloseEmotionsPopup}
            context={context}
            additionalNodes={['.dpdesignportal-chat-form-button-row-emoticons']}>

            <EmotionsPopup onClick={this.onSelectEmotion} />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
