import PropTypes from 'prop-types';
import React from 'react';
import MediumEditor from 'medium-editor';
import $ from 'jquery';
import {
  clipboardHasImages,
  clipboardIEHasImages,
  getBlobsFromItems,
  getBlobsFromIEItems,
  getBlobsFromHtml,
  getBlobFromUrl
} from 'DeskPRO/Component/Uploader/PasteCatcher';

export default class RteEditor extends React.Component {

  static propTypes = {
    tag:             PropTypes.string,
    value:           PropTypes.string,
    inline:          PropTypes.bool,
    ctrlEnterSubmit: PropTypes.bool,
    options:         PropTypes.object,
    onChange:        PropTypes.func,
    onSubmit:        PropTypes.func,
    onPasteImage:    PropTypes.func,
    onFocus:         PropTypes.func,
    onBlur:          PropTypes.func
  };

  componentDidMount() {
    const { inline, ctrlEnterSubmit, value = '', options = {} } = this.props;
    const {
      onChange = () => {},
      onSubmit = () => {},
      onFocus = () => {},
      onBlur = () => {}
    } = this.props;

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
    node.addEventListener('paste', this.onPaste);
    const overrideOptions = {
      paste: { cleanPastedHTML: false, forcePlainText: false }
    };

    this.medium = new MediumEditor(node, { ...options, ...overrideOptions });
    this.medium.setContent(value);
    this.medium.subscribe('editableInput', onChangeContent);
    this.medium.subscribe('onChange', onChangeContent);
    this.medium.subscribe('focus', onFocus);
    this.medium.subscribe('blur', onBlur);
    this.medium.subscribe('editableKeydownEnter', (event) => {
      const ctrlKey = event.ctrlKey || event.metaKey;

      if ((inline && !event.altKey && !ctrlKey && !event.shiftKey) || (ctrlEnterSubmit && ctrlKey)) {
        onSubmit(event, node.innerHTML);
      }
    });
    this.medium.subscribe('editableClick', () => {
      setTimeout(() => this.medium.startSelectionUpdates(), 1);
    });

    this.getDocument().addEventListener('mousemove', this.onMouseMove, true);

    if (options.toolbar) {
      node.addEventListener('focus', this.onShowToolbar, true);
    }
  }

  componentWillReceiveProps(newProps) {
    if (this.getNode() && newProps.value !== this.getNode().innerHTML) {
      let content = newProps.value;
      if (!content) {
        content = '<p></p>';
      }

      this.medium.setContent(content);
    }
  }

  componentWillUnmount() {
    this.getNode().removeEventListener('paste', this.onPaste);
    this.medium.destroy();
  }

  onMouseMove = () => {
    this.mousePresent = true;
    this.getDocument().removeEventListener('mousemove', this.onMouseMove, true);
  };

  onPaste = (event) => {
    event.preventDefault();
    event.stopPropagation();

    const { onPasteImage } = this.props;
    const clipboardData = event.clipboardData;
    if (clipboardData) {
      // Non-IE browsers
      if (!clipboardHasImages(clipboardData)) {
        let pastedText = clipboardData.getData('text/plain');
        if (pastedText) {
          pastedText = pastedText.replace(/\n/g, '<br />');

          this.medium.cleanPaste(pastedText);
        }
      }

      if (onPasteImage) {
        const pastedHtml = clipboardData.getData('text/html');
        if (clipboardData.items) {
          getBlobsFromItems(clipboardData.items, onPasteImage);
        } else if (pastedHtml) {
          getBlobsFromHtml(pastedHtml, onPasteImage);
        }
      }
    } else if (window.clipboardData) {
      // IE browser
      if (!clipboardIEHasImages(window.clipboardData)) {
        let content = window.clipboardData.getData('Text');
        if (content) {
          try {
            getBlobFromUrl(content, onPasteImage);
          } catch (e) {
            content = content.replace(/\n/g, '<br />');
            this.medium.cleanPaste(content);
          }
        }
      } else {
        getBlobsFromIEItems(window.clipboardData.files, event, onPasteImage);
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
    return this.node;
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
    const props = Object.assign({}, this.props);
    delete props.onPasteImage;
    delete props.ctrlEnterSubmit;
    delete props.options;
    return React.createElement(tag, { ...props, ref: (c) => { this.node = c; } });
  }
}
