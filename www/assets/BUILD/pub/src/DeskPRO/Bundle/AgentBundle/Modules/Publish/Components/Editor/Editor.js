import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import 'froala-editor/js/froala_editor.pkgd.min';
import classNames from 'classnames';
import $ from 'jquery';
import FroalaEditor from 'react-froala-wysiwyg';
import MarkdownEditor from './MarkdownEditor';
import * as actions from '../../Actions/publishEditorActions';

@connect()
export class EditorContainer extends React.Component {
  static propTypes = {
    value:        PropTypes.string,
    inputType:    PropTypes.string,
    hideEditor:   PropTypes.oneOfType([PropTypes.func, PropTypes.bool]),
    save:         PropTypes.oneOfType([PropTypes.func, PropTypes.bool]),
    updateSource: PropTypes.func,
    dispatch:     PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false,
      saved:  false
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpTopicSaving', () => {
      this.setState({
        saving: true
      });
    });
    window.document.addEventListener('dpTopicSaved', () => {
      this.setState({
        saving: false,
        saved:  true
      });
      setTimeout(() => { this.setState({ saved: false }); }, 3000);
    });
  };

  onAddFile = (data, callback) => {
    this.setState({
      saving: true
    });
    this.props.dispatch(actions.uploadFile(data, callback)).then(() => {
      this.setState({
        saving: false
      });
    });
  };

  onCancel = () => {
    if (this.props.hideEditor) {
      this.props.hideEditor();
    }
  };

  loadRemoteImages = (data, callback) => {
    this.setState({
      saving: true
    });
    this.props.dispatch(actions.loadRemoteImages(data, callback)).then(() => {
      this.setState({
        saving: false
      });
    });
  };

  render() {
    return (<Editor
      value={this.props.value}
      inputType={this.props.inputType}
      onCancel={this.onCancel}
      onSave={this.props.save}
      saving={this.state.saving}
      saved={this.state.saved}
      onAddFile={this.onAddFile}
      loadRemoteImages={this.loadRemoteImages}
      updateSource={this.props.updateSource}
      hideEditor={this.props.hideEditor}
    />);
  }
}
export class Editor extends React.Component {
  static propTypes = {
    value:            PropTypes.string,
    inputType:        PropTypes.string,
    onSave:           PropTypes.oneOfType([PropTypes.func, PropTypes.bool]),
    saving:           PropTypes.bool,
    saved:            PropTypes.bool,
    onCancel:         PropTypes.func,
    onAddFile:        PropTypes.func,
    loadRemoteImages: PropTypes.func,
    updateSource:     PropTypes.func,
    hideEditor:       PropTypes.oneOfType([PropTypes.func, PropTypes.bool]),
  };
  static defaultProps = {
    value:     '',
    inputType: 'markdown',
    onSave() {},
    onCancel() {},
    onAddFile() {},
    updateSource() {},
  };

  constructor(props) {
    super(props);
    const inputType = this.props.inputType ? this.props.inputType : 'rte';
    let html;
    let markdown;
    if (inputType === 'rte') {
      html = this.props.value;
      markdown = MarkdownEditor.toMarkdown(this.props.value);
    } else {
      html = '';
      markdown = this.props.value;
    }
    this.state = {
      html,
      markdown,
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
    if (this.props.saving) {
      return false;
    }
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
    return true;
  };

  onChange = (value, html) => {
    this.setState({ markdown: value });
    if (!this.props.onSave) {
      this.props.updateSource(html, value, 'markdown');
    }
  };

  onContentChange = (content) => {
    this.setState({ html: content });
    if (!this.props.onSave) {
      this.props.updateSource(content, content, 'rte');
    }
  };

  getEditor = () => {
    switch (this.state.inputType) {
      case 'rte': {
        const froalaConfig = {
          imageUploadMethod: 'POST',
          imageUploadURL:    '/api/v2/blobs/froala',
          charCounterCount:  false,
          enter:             $.FroalaEditor.ENTER_BR,
          key:               'qENARBFSTb1G1QJg1RA=='
        };

        if (window.DeskPRO_Window) {
          froalaConfig.events = {
            'froalaEditor.focus': () => {
              window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
            },
            'froalaEditor.blur': () => {
              window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
            },
          };
        }
        return (<FroalaEditor
          config={froalaConfig}
          model={this.state.html}
          onModelChange={this.onContentChange}
        />);
      }
      case 'markdown':
      default: {
        return (
          <MarkdownEditor
            ref={(c) => {
              this.markdownEditor = c;
            }}
            value={this.state.markdown}
            onChange={this.onChange}
            onAddFile={this.props.onAddFile}
            loadRemoteImages={this.props.loadRemoteImages}
          />
        );
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
    return (
      <div className="publish-editor">
        <div className="header">
          {this.props.hideEditor ? <Button className="pull-right" confirm onClick={this.onCancel}>Cancel</Button> : ''}
          {this.props.onSave ?
            <Button
              className={classNames('pull-right', { loading: this.props.saving })}
              onClick={this.onSave}
            >
              {this.props.saved ? 'Saved' : 'Save'}
            </Button> : '' }
        </div>
        <div className="editor">
          {this.getEditor()}
        </div>
      </div>);
  }
}
