import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import MediumEditor from 'medium-editor';
import $ from 'jquery';
import { getBlobsFromItems, getBlobsFromHtml } from 'DeskPRO/Component/Uploader/PasteCatcher';

export class RteEditor extends React.Component {

  static propTypes = {
    tag: PropTypes.string,
    value: PropTypes.string,
    inline: PropTypes.bool,
    options: PropTypes.object,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func,
    onPasteImage: PropTypes.func
  };

  componentDidMount() {
    const { inline, value = '', options = {} } = this.props;
    const { onChange = () => {}, onSubmit = () => {} } = this.props;

    const node = ReactDOM.findDOMNode(this);
    const onChangeContent = () => {
      onChange(node.innerHTML);
    };

    // Override default paste listener to upload images
    $(node).on('paste', this.onPaste);
    const overrideOptions = {paste: {cleanPastedHTML: true}};

    this.medium = new MediumEditor(node, {...options, ...overrideOptions});
    this.medium.setContent(value);
    this.medium.subscribe('editableInput', onChangeContent);
    this.medium.subscribe('onChange', onChangeContent);
    this.medium.subscribe('editableKeydownEnter', event => {
      if (inline && !event.altKey && !event.ctrlKey && !event.shiftKey) {
        onSubmit(event, node.innerHTML);
      }
    });
    this.medium.subscribe('initialFocus', () => {
      this.medium.selectElement(node);
    });
    this.medium.subscribe('clearEmptyContent', () => {
      if (node.innerHTML === '<p><br></p>') {
        node.innerHTML = '';
      }
    });
  }

  componentWillReceiveProps(newProps) {
    const node = ReactDOM.findDOMNode(this);

    if (newProps.value !== node.innerHTML) {
      let content = newProps.value;
      if (!content) {
        content = '<p><br></p>';
      }

      this.medium.setContent(content);
    }
  }

  componentWillUnmount() {
    const node = ReactDOM.findDOMNode(this);
    $(node).off('paste', this.onPaste);

    this.medium.destroy();
  }

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

  getMediumEditor() {
    return this.medium;
  }

  getContent() {
    return ReactDOM.findDOMNode(this).innerHTML;
  }

  setContent(html) {
    this.medium.setContent(html);
  }

  pasteHtml(html) {
    this.medium.pasteHTML(html);
  }

  focus() {
    this.medium.restoreSelection();

    if (!this.medium.checkSelection().selectionState) {
      this.medium.trigger('initialFocus');

      const contentWindow = this.medium.options.contentWindow;
      if (contentWindow.getSelection) {
        contentWindow.getSelection().collapseToEnd();
      }
    }
  }

  render() {
    const { tag = 'div' } = this.props;
    return React.createElement(tag, this.props);
  }
}
