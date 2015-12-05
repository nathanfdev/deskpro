import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import MediumEditor from 'medium-editor';

export default class RteInput extends React.Component {

  static propTypes = {
    tag: PropTypes.string,
    value: PropTypes.string,
    inline: PropTypes.bool,
    options: PropTypes.object,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    const { inline, value = '', options = {} } = this.props;
    const { onChange = () => {}, onSubmit = () => {} } = this.props;

    const node = ReactDOM.findDOMNode(this);

    this.medium = new MediumEditor(node, options);
    this.medium.setContent(value);
    this.medium.subscribe('editableInput', () => onChange(node.innerHTML));
    this.medium.subscribe('editableKeydownEnter', event => {
      if (inline && !event.altKey && !event.ctrlKey && !event.shiftKey) {
        onSubmit(event, node.innerHTML);
      }
    });
  }

  componentWillReceiveProps(newProps) {
    const node = ReactDOM.findDOMNode(this);

    if (newProps.value !== node.innerHTML) {
      this.medium.setContent('<p><br></p>');
    }
  }

  componentWillUnmount() {
    this.medium.destroy();
  }

  render() {
    const { tag = 'div' } = this.props;
    return React.createElement(tag, this.props);
  }
}
