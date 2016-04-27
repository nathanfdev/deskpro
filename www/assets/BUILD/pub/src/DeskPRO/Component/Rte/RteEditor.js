import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import MediumEditor from 'medium-editor';
import $ from 'jquery';
import { getBlobsFromItems, getBlobsFromHtml } from 'DeskPRO/Component/Uploader/PasteCatcher';

export class RteEditor extends React.Component {

  static propTypes = {
    tag:          PropTypes.string,
    value:        PropTypes.string,
    inline:       PropTypes.bool,
    options:      PropTypes.object,
    onChange:     PropTypes.func,
    onSubmit:     PropTypes.func,
    onPasteImage: PropTypes.func
  };

  componentDidMount() {
    const { inline, value = '', options = {} } = this.props;
    const { onChange = () => {}, onSubmit = () => {} } = this.props;

    const node = this.getNode();
    const onChangeContent = () => {
      // remove empty blocks
      $('p', node).each((i, p) => {
        const $p = $(p);
        if (!$p.html()) {
          $p.remove();
        }
      });

      // wrap content
      if (!$('p', node).length) {
        node.innerHTML = `<p>${node.innerHTML}</p>`;
        // refocus after the modification
        this.focus();
      }

      onChange(node.innerHTML);
    };

    // Override default paste listener to upload images
    $(node).on('paste', this.onPaste);
    const overrideOptions = {
      paste: { cleanPastedHTML: true }
    };

    this.medium = new MediumEditor(node, { ...options, ...overrideOptions });
    this.medium.setContent(value);
    this.medium.subscribe('editableInput', onChangeContent);
    this.medium.subscribe('onChange', onChangeContent);
    this.medium.subscribe('editableKeydownEnter', event => {
      if (inline && !event.altKey && !event.ctrlKey && !event.shiftKey) {
        onSubmit(event, node.innerHTML);
      }
    });
    this.medium.subscribe('editableClick', () => {
      setTimeout(() => this.medium.startSelectionUpdates(), 1);
    });

    this.getDocument().addEventListener('mousemove', this.onMouseMove, true);
    node.addEventListener('focus', this.onShowToolbar, true);
  }

  componentWillReceiveProps(newProps) {
    if (newProps.value !== this.getNode().innerHTML) {
      let content = newProps.value;
      if (!content) {
        content = '<p><br></p>';
      }

      this.medium.setContent(content);
    }
  }

  componentWillUnmount() {
    $(this.getNode()).off('paste', this.onPaste);
    this.medium.destroy();
  }

  onMouseMove = () => {
    this.mousePresent = true;
    this.getDocument().removeEventListener('mousemove', this.onMouseMove, true);
  };

  onPaste = event => {
    event.preventDefault();
    event.stopPropagation();

    const clipboardData = event.originalEvent.clipboardData;
    const pastedText = clipboardData.getData('text/plain');
    const pastedHtml = clipboardData.getData('text/html');

    this.medium.cleanPaste(pastedText);

    const { onPasteImage } = this.props;
    if (onPasteImage) {
      if (clipboardData.items) {
        getBlobsFromItems(clipboardData.items, onPasteImage);
      } else if (pastedHtml) {
        getBlobsFromHtml(pastedHtml, onPasteImage);
      }
    }
  };

  onShowToolbar = () => {
    if (this.mousePresent) {
      $('.medium-editor-toolbar').show();
      $('.dp-medium-rte').addClass('with-toolbar');
      this.getNode().removeEventListener('focus', this.onShowToolbar, true);
    }
  };

  getMediumEditor() {
    return this.medium;
  }

  getNode() {
    return ReactDOM.findDOMNode(this);
  }

  getDocument() {
    return this.medium.options.ownerDocument;
  }

  getContent() {
    return this.getNode().innerHTML;
  }

  setContent(html) {
    this.medium.setContent(html);
  }

  pasteHtml(html, options) {
    this.medium.pasteHTML(html, options);
  }

  focus() {
    this.prepareFocusContent();

    const doc = this.getDocument();
    const node = this.getNode();

    this.medium.restoreSelection();
    if (this.medium.checkSelection().selectionState) {
      // has stored selection
      if (doc.getSelection) {
        const sel = doc.getSelection();
        if (sel.focusNode === node) {
          // focus outside the <p> tag
          // could cause for empty content
          this.focusEnd();
        } else {
          const range = sel.getRangeAt(0);
          range.collapse(false);

          sel.removeAllRanges();
          sel.addRange(range);
        }
      }
    } else {
      // no selection, move caret to end
      this.focusEnd();
    }
  }

  focusEnd() {
    this.prepareFocusContent();

    const doc = this.getDocument();
    const node = this.getNode();
    const $p = $('p', node);

    if (doc.getSelection) {
      const range = doc.createRange();
      range.selectNodeContents($p.last().get(0));
      range.collapse(false);

      const sel = doc.getSelection();
      sel.removeAllRanges();
      sel.addRange(range);
    }

    $(node).focus();
  }

  prepareFocusContent() {
    const node = this.getNode();
    const $p = $('p', node);

    if (!$p.length || node.innerHTML === '<p><br></p>') {
      node.innerHTML = '<p></p>';
    }
  }

  render() {
    const { tag = 'div' } = this.props;
    return React.createElement(tag, this.props);
  }
}
