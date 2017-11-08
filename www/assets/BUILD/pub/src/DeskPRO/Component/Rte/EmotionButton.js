import PropTypes from 'prop-types';
import React from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import { EmotionsPopup } from './EmotionsPopup';
import { createEmotionImage } from './Emotions';

export class EmotionButton extends React.Component {

  static propTypes = {
    getEditor:       PropTypes.func,
    context:         PropTypes.any, // eslint-disable-line
    className:       PropTypes.string,
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

  onSelectEmoticon = (event) => {
    event.preventDefault();

    const medium = this.props.getEditor().getMediumEditor();
    medium.saveSelection();

    this.setState({
      emotionsPopup: true
    });
  };

  onCloseEmotionsPopup = () => {
    const medium = this.props.getEditor().getMediumEditor();
    medium.restoreSelection();

    this.setState({
      emotionsPopup: false
    });
  };

  onSelectEmotion = (code) => {
    this.onCloseEmotionsPopup();

    const editor = this.props.getEditor();
    const medium = editor.getMediumEditor();
    medium.stopSelectionUpdates();

    // focus the rte field
    editor.focus();

    const contentWindow = medium.options.contentWindow;
    const ownerDocument = medium.options.ownerDocument;

    const html = ` ${createEmotionImage(code)} `;

    if (contentWindow.getSelection) {
      // IE9 and non-IE
      const selection = contentWindow.getSelection();
      if (selection.getRangeAt && selection.rangeCount) {
        let range = selection.getRangeAt(0);
        range.deleteContents();

        // Range.createContextualFragment() would be useful here but is
        // only relatively recently standardized and is not supported in
        // some browsers (IE9, for one)
        const el     = ownerDocument.createElement('div');
        el.innerHTML = html;
        const frag   = ownerDocument.createDocumentFragment();

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
          range = ownerDocument.createRange();
          range.selectNodeContents(lastNode);
          range.collapse(false);

          selection.removeAllRanges();
          selection.addRange(range);
        }
      }
    } else if (ownerDocument.selection && ownerDocument.selection.type !== 'Control') {
      // IE < 9
      ownerDocument.selection.createRange().pasteHTML(html);
    }

    medium.saveSelection();
    medium.trigger('onChange');

    // focus the rte again to correct display caret position
    editor.focus();
  };

  render() {
    const { className, buttonClassName, context, popupPositionAt, popupPositionMy } = this.props;

    return (
      <span className={className}>
        <a
          className="dpdesignportal-chat-form-button dpdesignportal-chat-form-button-row-emoticons"
          title="Chat Emoticons"
          onClick={this.onSelectEmoticon}
        >
          <span className={buttonClassName} ref={(c) => { this.emotionsButton = c; }} />
        </a>

        <Simple
          isOpen={this.state.emotionsPopup}
          positionTarget={this.emotionsButton}
          positionAt={popupPositionAt}
          positionMy={popupPositionMy}
          zIndex={1000}
        >

          <ClickOut
            onClickOut={this.onCloseEmotionsPopup}
            context={context}
            additionalNodes={['.dpdesignportal-chat-form-button-row-emoticons']}
          >

            <EmotionsPopup onClick={this.onSelectEmotion} />
          </ClickOut>
        </Simple>
      </span>
    );
  }
}
