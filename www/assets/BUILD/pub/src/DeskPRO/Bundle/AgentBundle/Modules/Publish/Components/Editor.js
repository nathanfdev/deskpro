import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Button, ButtonGroup } from 'DeskPRO/Component/Semantic/Button';
import MarkdownEditor from 'DeskPRO/Component/Markdown/MarkdownEditor';
import 'froala-editor/js/froala_editor.pkgd.min';
import $ from 'jquery';
import FroalaEditor from 'react-froala-wysiwyg';
import * as actions from './../Actions/publishEditorActions';

@connect()
export class EditorContainer extends React.Component {
  static propTypes = {
    value:      PropTypes.string,
    inputType:  PropTypes.string,
    hideEditor: PropTypes.func,
    save:       PropTypes.func,
    dispatch:   PropTypes.func,
  };

  onAddFile = (data, callback) => {
    this.props.dispatch(actions.uploadFile(data, callback));
  };

  onCancel = () => {
    this.props.hideEditor();
  };

  loadRemoteImages = (data, callback) => {
    this.props.dispatch(actions.loadRemoteImages(data, callback));
  };

  render() {
    return (<Editor
      value={this.props.value}
      inputType={this.props.inputType}
      onCancel={this.onCancel}
      onSave={this.props.save}
      onAddFile={this.onAddFile}
      loadRemoteImages={this.loadRemoteImages}
    />);
  }
}
export class Editor extends React.Component {
  static propTypes = {
    value:            PropTypes.string,
    inputType:        PropTypes.string,
    onSave:           PropTypes.func,
    onCancel:         PropTypes.func,
    onAddFile:        PropTypes.func,
    loadRemoteImages: PropTypes.func
  };
  static defaultProps = {
    value:     '',
    inputType: 'markdown',
    onSave() {},
    onCancel() {},
    onAddFile() {}
  };

  constructor(props) {
    super(props);
    const inputType = this.props.inputType ? this.props.inputType : 'rte';
    this.state = {
      html:     this.props.value,
      markdown: MarkdownEditor.toMarkdown(this.props.value),
      inputType
    };
  }

  onCancel = () => {
    this.props.onCancel();
    this.setState({
      html:     this.props.value,
      markdown: MarkdownEditor.toMarkdown(this.props.value)
    });
  };

  onSave = () => {
    let input;
    let html;
    if (this.state.inputType === 'rte') {
      input = this.state.html;
      html = input;
    } else {
      html = this.markdownEditor.renderHtml(this.state.markdown);
      input = this.state.markdown;
    }
    this.props.onSave(html, input, this.state.inputType);
  };

  onChange = (value) => {
    this.setState({ markdown: value });
  };

  onContentChange = (content) => {
    this.setState({ html: content });
  };

  getEditor = () => {
    switch (this.state.inputType) {
      case 'markdown': {
        return (
          <MarkdownEditor
            ref={(c) => { this.markdownEditor = c; }}
            value={this.state.markdown}
            onChange={this.onChange}
            onAddFile={this.props.onAddFile}
            loadRemoteImages={this.props.loadRemoteImages}
          />
        );
      }
      case 'rte':
      default: {
        const froalaConfig = {
          imageUploadMethod: 'POST',
          imageUploadURL:    '/api/v2/blobs/froala',
          charCounterCount:  false,
          enter:             $.FroalaEditor.ENTER_BR,
          key:               'qENARBFSTb1G1QJg1RA=='
        };

        if (window.DeskPRO_Window) {
          froalaConfig.events = {
            'froalaEditor.focus': () => { window.DeskPRO_Window.keyboardShortcuts.isPaused = true; },
            'froalaEditor.blur':  () => { window.DeskPRO_Window.keyboardShortcuts.isPaused = false; },
          };
        }
        return (<FroalaEditor
          config={froalaConfig}
          model={this.state.html}
          onModelChange={this.onContentChange}
        />);
      }
    }
  };

  changeMode = (type) => {
    if (type !== this.state.inputType) {
      this.setState({ inputType: type });
      if (type === 'markdown') {
        this.setState({ markdown: MarkdownEditor.toMarkdown(this.state.html) });
      } else {
        this.setState({ html: this.markdownEditor.renderHtml(this.state.markdown) });
      }
    }
  };

  render() {
    return (<div className="publish-editor">
      <div className="header">
        <ButtonGroup onChange={this.changeMode} activeKey={this.state.inputType}>
          <Button key="markdown">Markdown</Button>
          <Button key="rte">Classic</Button>
        </ButtonGroup>
        <Button className="pull-right" confirm onClick={this.onCancel}>Cancel</Button>
        <Button className="pull-right" onClick={this.onSave}>Save</Button>
      </div>
      <div className="editor">
        {this.getEditor()}
      </div>
    </div>);
  }
}
