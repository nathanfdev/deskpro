import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import MediumEditor from 'medium-editor';

export default class RteInput extends React.Component {

  static propTypes = {
    tag: PropTypes.string,
    value: PropTypes.string,
    options: PropTypes.object,
    onChange: PropTypes.func
  };

  componentDidMount() {
    const { value = '', options = {}, onChange } = this.props;
    const node = ReactDOM.findDOMNode(this);

    this.medium = new MediumEditor(node, options);
    this.medium.subscribe('editableInput', () => onChange(node.innerHTML));
    this.medium.setContent(value);
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
