import React, { PropTypes } from 'react';
import { Button, ButtonGroup } from 'DeskPRO/Component/Semantic/Button';
import 'froala-editor/js/froala_editor.pkgd.min';
import 'froala-editor/css/froala_editor.pkgd.css';
import $ from 'jquery';
import FroalaEditor from 'react-froala-wysiwyg';
import ReactMarkdownEditor from 'react-markdown-editor';
import toMarkdown from 'to-markdown';

export class EditorContainer extends React.Component {
  static propTypes = {
    value: PropTypes.string
  };

  render() {
    return <Editor value={this.props.value} />;
  }
}
export class Editor extends React.Component {
  static propTypes = {
    value: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      message:    '',
      markdown:   '',
      editorMode: 'classic'
    };
  }

  onChange = (event) => {
    this.onContentChange(event.target.value);
  };

  onMarkdownChange = (content) => {
    this.setState({ markdown: content });
  };

  onContentChange = (content) => {
    this.setState({ message: content });
  };

  getEditor = () => {
    switch (this.state.editorMode) {
      case 'raw': {
        return <textarea cols="100" rows="20" value={this.state.message} onChange={this.onChange} />;
      }
      case 'markdown': {
        const MarkdownEditor = ReactMarkdownEditor.MarkdownEditor;
        return (<MarkdownEditor
          initialContent={this.state.markdown}
          onContentChange={this.onMarkdownChange}
          iconsSet="font-awesome"
        />);
      }
      case 'classic':
      default: {
        const froalaConfig = {
          // toolbarInline: true,
          charCounterCount: false,
          enter:            $.FroalaEditor.ENTER_BR,
        };

        if (window.DeskPRO_Window) {
          froalaConfig.events = {
            'froalaEditor.focus': () => { window.DeskPRO_Window.keyboardShortcuts.isPaused = true; },
            'froalaEditor.blur':  () => { window.DeskPRO_Window.keyboardShortcuts.isPaused = false; }
          };
        }
        return (<FroalaEditor
          config={froalaConfig}
          model={this.state.message}
          onModelChange={this.onContentChange}
        />);
      }
    }
  };

  changeMode = (mode) => {
    this.setState({ editorMode: mode });
    if (mode === 'markdown') {
      const converters = [
        {
          filter: 'u',
          replacement(content) {
            return `_${content}_`;
          }
        },
        {
          filter: 'i',
          replacement(content) {
            return `*${content}*`;
          }
        }
      ];
      this.setState({ markdown: toMarkdown(this.state.message, { converters, gfm: true }) });
    }
  };

  render() {
    return (<div className="publish-editor">
      <div className="header">
        <ButtonGroup onChange={this.changeMode} activeKey={this.state.editorMode}>
          <Button key="classic">Classic</Button>
          <Button key="markdown">Markdown</Button>
          <Button key="raw">Raw</Button>
        </ButtonGroup>
      </div>
      <div className="editor">
        {this.getEditor()}
      </div>
    </div>);
  }
}
