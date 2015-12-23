import React, { PropTypes } from 'react';
import { EmotionsPopup } from './EmotionsPopup';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import * as Emotions from './Emotions';

export default class EmotionButton extends React.Component {

  static propTypes = {
    getEditor: PropTypes.func,
    onSelect: PropTypes.func,
    context: PropTypes.any,
    className: PropTypes.string,
    buttonClassName: PropTypes.string,
    popupPositionAt: PropTypes.string,
    popupPositionMy: PropTypes.string
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

      if (contentWindow.getSelection) {
        contentWindow.getSelection().collapseToEnd();
      }
    }

    const html = ` ${Emotions.createEmotionImage(code)} `;

    if (contentWindow.getSelection) {
      // IE9 and non-IE
      const selection = contentWindow.getSelection();
      if (selection.getRangeAt && selection.rangeCount) {
        let range = selection.getRangeAt(0);
        range.deleteContents();

        // Range.createContextualFragment() would be useful here but is
        // only relatively recently standardized and is not supported in
        // some browsers (IE9, for one)
        var el = document.createElement('div');
        el.innerHTML = html;
        const frag = document.createDocumentFragment();

        let node;
        let lastNode;

        do {
          node = el.firstChild;
          if (node) {
            lastNode = frag.appendChild(node);
          }
        } while (node);

        range.insertNode(frag);

        // Preserve the selection
        if (lastNode) {
          range = range.cloneRange();
          range.setStartAfter(lastNode);
          range.collapse(true);

          selection.removeAllRanges();
          selection.addRange(range);
        }
      }
    } else if (ownerDocument.selection && ownerDocument.selection.type !== 'Control') {
      // IE < 9
      ownerDocument.selection.createRange().pasteHTML(html);
    }

    medium.saveSelection();
    medium.trigger('editableKeypress', {which: ''.charCodeAt(0)});

    this.onCloseEmotionsPopup();
  };

  render() {
    const { className, buttonClassName, context, popupPositionAt, popupPositionMy } = this.props;

    return (
      <span className={className}>
        <a href="#"
           className="dpdesignportal-chat-form-button dpdesignportal-chat-form-button-row-emoticons"
           title="Chat Emoticons"
           onClick={this.onSelectEmoticon}>

          <span className={buttonClassName} ref="emotionsButton" />
        </a>

        <Simple
          isOpen={this.state.emotionsPopup}
          positionTarget={this.refs.emotionsButton}
          positionAt={popupPositionAt}
          positionMy={popupPositionMy}
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
