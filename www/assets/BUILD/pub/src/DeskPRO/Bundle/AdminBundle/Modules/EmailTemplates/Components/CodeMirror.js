import React, { PropTypes } from 'react';
import CM from 'codemirror';
import 'codemirror/mode/twig/twig';
import 'codemirror/mode/htmlembedded/htmlembedded';
import 'codemirror/addon/mode/overlay';
import $ from 'jquery';

class CodeMirror extends React.Component {
  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func,
    options:  PropTypes.object,
  };
  static defaultProps = {
    onChange() {},
    value: '',
  };

  constructor(props) {
    super(props);
    this.state = {
      isFocused: false,
    };
  }

  componentDidMount() {
    this.codeMirror = CM.fromTextArea(this.codeMirrorNode, this.getOptions());
    this.codeMirror.on('change', this.codemirrorValueChanged);
    this.codeMirror.on('focus', this.focusChanged.bind(this, true));
    this.codeMirror.on('blur', this.focusChanged.bind(this, false));
    this.currentCodemirrorValue = this.props.value;

    const that = this;
    setTimeout(() => {
      that.codeMirror.refresh();
    }, 1);

    this.codeMirror.on('beforeChange', (event) => {
      console.log('beforeChange event');
      console.log(event);
    });

    $('.dp-code-editor').on('click', '.twig-variable', (e) => {
      console.log(e);
      console.log(e.target);
    });
  }

  componentWillReceiveProps(nextProps) {
    if (this.codeMirror && this.currentCodemirrorValue !== nextProps.value) {
      this.currentCodemirrorValue = nextProps.value;
      this.codeMirror.setValue(nextProps.value);

      const that = this;
      setTimeout(() => {
        that.codeMirror.refresh();
      }, 500);
    }
  }

  componentWillUnmount() {
    // todo: is there a lighter-weight way to remove the cm instance?
    if (this.codeMirror) {
      this.codeMirror.toTextArea();
    }
  }

  getOptions() {
    return Object.assign({
      mode:           'twig',
      lineNumbers:    true,
      theme:          'monokai',
      lineWrapping:   true,
      indentWithTabs: true,
      tabSize:        '2',
    }, this.props.options);
  }

  getCodeMirror() {
    return this.codeMirror;
  }

  focusChanged(focused) {
    this.setState({ isFocused: focused });
  }

  codemirrorValueChanged = (doc) => {
    const newValue = doc.getValue();
    this.currentCodemirrorValue = newValue;
    this.props.onChange(newValue);
  };

  render() {
    return (
      <textarea
        ref={(c) => { this.codeMirrorNode = c; }}
        defaultValue={this.props.value}
        autoComplete="off"
      />
    );
  }
}
export default CodeMirror;
