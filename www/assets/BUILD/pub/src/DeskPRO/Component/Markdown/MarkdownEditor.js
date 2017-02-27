import React from 'react';
import classNames from 'classnames';
import CM from 'codemirror';
import MarkdownIt from 'markdown-it';
import toMarkdown from 'to-markdown';
import 'inline-attachment/src/inline-attachment';

import 'codemirror/mode/xml/xml';
import 'codemirror/mode/markdown/markdown';
import 'codemirror/addon/edit/continuelist';

import { getCursorState, applyFormat } from './format';
import * as Icons from './Icons';

class MarkdownEditor extends React.Component {
  static propTypes = {
    onChange:         React.PropTypes.func,
    onAddFile:        React.PropTypes.func,
    loadRemoteImages: React.PropTypes.func,
    options:          React.PropTypes.object,
    path:             React.PropTypes.string,
    value:            React.PropTypes.string,
  };
  static defaultProps = {
    onChange() {},
    onAddFile() {}
  };

  static renderIcon(icon) {
    return <span className="MDEditor_toolbarButton_icon">{icon}</span>;
  }

  constructor(props) {
    super(props);
    this.md = new MarkdownIt({
      html:        false,
      linkify:     true,
      typographer: true
    });
    this.state = {
      isFocused: false,
      cs:        {},
      html:      this.md.render(props.value)
    };
  }

  componentDidMount() {
    this.codeMirror = CM.fromTextArea(this.codeMirrorNode, this.getOptions());
    this.codeMirror.on('change', this.codemirrorValueChanged);
    this.codeMirror.on('focus', this.focusChanged.bind(this, true));
    this.codeMirror.on('blur', this.focusChanged.bind(this, false));
    this.codeMirror.on('cursorActivity', this.updateCursorState);
    this.codeMirror.on('paste', this.onPaste);
    this.codeMirror.on('drop', this.onDrop);
    this.currentCodemirrorValue = this.props.value;
  }

  componentWillReceiveProps(nextProps) {
    if (this.codeMirror && this.currentCodemirrorValue !== nextProps.value) {
      this.codeMirror.setValue(nextProps.value);
    }
  }

  componentWillUnmount() {
    // todo: is there a lighter-weight way to remove the cm instance?
    if (this.codeMirror) {
      this.codeMirror.toTextArea();
    }
  }

  onPaste = (cm, event) => {
    const data = (event.clipboardData || event.originalEvent.clipboardData);
    if (data.types.indexOf('text/html') > -1) {
      this.importHtml(data.getData('text/html'));
      event.preventDefault();
    } else {
      this.getItemsToUpload(data.items);
    }
  };

  onDrop = (cm, event) => {
    event.preventDefault();
    const items = (event.dataTransfer || event.originalEvent.dataTransfer).items;
    this.getItemsToUpload(items);
  };

  getOptions() {
    return Object.assign({
      mode:           'markdown',
      lineNumbers:    false,
      indentWithTabs: true,
      tabSize:        '2',
    }, this.props.options);
  }

  getCodeMirror() {
    return this.codeMirror;
  }

  getItemsToUpload = (items) => {
    for (const item of Array.values(items)) {
      if ((item.kind === 'file') && (item.type.match('^image/'))) {
        // Drag data item is an image file
        const blob = item.getAsFile();
        if (blob) {
          event.preventDefault();
          const formData = new FormData();
          formData.append('name', blob.name);

          const reader = new FileReader();
          reader.onload = (event) => {
            formData.append('file', event.target.result);
            this.props.onAddFile(formData, this.appendImage);
          };
          reader.readAsDataURL(blob);
        }
      }
    }
  };

  importHtml = (html) => {
    const markdown = toMarkdown(
      html,
      {
        gfm:        true,
        converters: [
          {
            filter: ['div', 'span'],
            replacement(content) {
              return content;
            }
          }
        ]
      });
    this.props.onChange(markdown);
    this.parseImages(markdown);
  };

  parseImages = (markdown) => {
    const images = [];
    markdown.replace(/!\[[^\]]+]\(([^)]+)\)/g, (m, key) => {
      images.push({
        match:  m,
        source: key
      });
    });
    this.props.loadRemoteImages(images, blobs => this.replaceRemoteImages(markdown, blobs));
  };

  replaceRemoteImages = (markdown, blobs) => {
    let result = markdown;
    blobs.forEach((element) => {
      const image = element.match.replace(element.source, element.blob.download_url);
      result = result.replace(element.match, image);
    });
    this.props.onChange(result);
  };

  appendImage = (data) => {
    let code = `[${data.filename}](${data.download_url})`;
    if (data.content_type.match(/^image/)) {
      code = `!${code}`;
    }
    this.codeMirror.replaceSelection(code);
  };

  focus() {
    if (this.codeMirror) {
      this.codeMirror.focus();
    }
  }

  focusChanged(focused) {
    this.setState({ isFocused: focused });
  }

  updateCursorState = () => {
    this.setState({ cs: getCursorState(this.codeMirror) });
  };

  codemirrorValueChanged = (doc) => {
    const newValue = doc.getValue();
    this.currentCodemirrorValue = newValue;
    this.setState({ html: this.md.render(newValue) });
    this.props.onChange(newValue);
  };

  toggleFormat(formatKey, e) {
    e.preventDefault();
    applyFormat(this.codeMirror, formatKey);
  }

  renderButton(formatKey, label, action) {
    const onClickAction = (!action) ? this.toggleFormat.bind(this, formatKey) : action;

    const isTextIcon = (formatKey === 'h1' || formatKey === 'h2' || formatKey === 'h3');
    const className = classNames('MDEditor_toolbarButton', {
      'MDEditor_toolbarButton--pressed': this.state.cs[formatKey]
    }, (`MDEditor_toolbarButton--${formatKey}`));

    const labelClass = isTextIcon ? 'MDEditor_toolbarButton_label-icon' : 'MDEditor_toolbarButton_label';

    return (
      <button className={className} onClick={onClickAction} title={formatKey}>
        {isTextIcon ? null : MarkdownEditor.renderIcon(Icons[formatKey])}
        <span className={labelClass}>{label}</span>
      </button>
    );
  }

  renderToolbar() {
    return (
      <div className="MDEditor_toolbar">
        {this.renderButton('h1', 'h1')}
        {this.renderButton('h2', 'h2')}
        {this.renderButton('h3', 'h3')}
        {this.renderButton('bold', 'b')}
        {this.renderButton('italic', 'i')}
        {this.renderButton('oList', 'ol')}
        {this.renderButton('uList', 'ul')}
        {this.renderButton('quote', 'q')}
        {/* this.renderButton('link', 'a')*/}
      </div>
    );
  }

  render() {
    const editorClassName = classNames('MDEditor_editor', { 'MDEditor_editor--focused': this.state.isFocused });
    return (
      <div className="MDEditor">
        {this.renderToolbar()}
        <div className={editorClassName}>
          <textarea
            ref={(c) => { this.codeMirrorNode = c; }}
            name={this.props.path}
            defaultValue={this.props.value}
            autoComplete="off"
          />
        </div>
        <h3>Preview</h3>
        <div className="preview" dangerouslySetInnerHTML={{ __html: this.state.html }} />
      </div>
    );
  }
}

export default MarkdownEditor;
