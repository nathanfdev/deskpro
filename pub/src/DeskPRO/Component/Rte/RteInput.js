import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import MediumEditor from 'medium-editor';
import $ from 'jquery';

export class RteInput extends React.Component {

  static propTypes = {
    tag: PropTypes.string,
    value: PropTypes.string,
    inline: PropTypes.bool,
    options: PropTypes.object,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func,
    getPasteCatcher: PropTypes.func
  };

  componentDidMount() {
    const { inline, value = '', options = {} } = this.props;
    const { onChange = () => {}, onSubmit = () => {} } = this.props;

    const node = ReactDOM.findDOMNode(this);
    const onChangeContent = () => {
      onChange(node.innerHTML);
    };

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

    const originalEvent = event.originalEvent;
    const paste = this.medium.getExtensionByName('paste');
    const pastedHTML = originalEvent.clipboardData.getData('text/html');

    paste.cleanPaste(pastedHTML);

    const { getPasteCatcher } = this.props;
    if (getPasteCatcher) {
      const pasteCatcher = getPasteCatcher();
      pasteCatcher.onPaste(event);
    }
  };

  getMediumEditor() {
    return this.medium;
  }

  render() {
    const { tag = 'div' } = this.props;
    return React.createElement(tag, this.props);
  }
}
