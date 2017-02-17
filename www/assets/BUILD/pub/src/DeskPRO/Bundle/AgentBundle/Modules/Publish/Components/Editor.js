import React, { PropTypes } from 'react';
import { Button, ButtonGroup } from 'DeskPRO/Component/Semantic/Button';
import MarkdownEditor from 'DeskPRO/Component/Markdown/MarkdownEditor';
import 'froala-editor/js/froala_editor.pkgd.min';
import $ from 'jquery';
import FroalaEditor from 'react-froala-wysiwyg';


export class EditorContainer extends React.Component {
  static propTypes = {
    value:          PropTypes.string,
    updateHtml:     PropTypes.func,
    updateMarkdown: PropTypes.func
  };

  render() {
    return (<Editor
      value={this.props.value}
      updateHtml={this.props.updateHtml}
      updateMarkdown={this.props.updateMarkdown}
    />);
  }
}
export class Editor extends React.Component {
  static propTypes = {
    value:      PropTypes.string,
    updateHtml: PropTypes.func
  };
  static defaultProps = {
    updateHtml() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      message:   this.props.value,
      markdown:  this.props.value,
      inputMode: 'rte'
    };
  }

  onChange = (event) => {
    this.setState({ markdown: event.target.value });
  };

  onContentChange = (content) => {
    this.setState({ message: content });
    this.props.updateHtml(content);
  };

  getEditor = () => {
    switch (this.state.inputMode) {
      case 'markdown': {
        return (<MarkdownEditor value={this.state.markdown} onChange={this.onChange} />);
      }
      case 'rte':
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
    this.setState({ inputMode: mode });
  };

  render() {
    return (<div className="publish-editor">
      <div className="header">
        <ButtonGroup onChange={this.changeMode} activeKey={this.state.inputMode}>
          <Button key="rte">Classic</Button>
          <Button key="markdown">Markdown</Button>
        </ButtonGroup>
      </div>
      <div className="editor">
        {this.getEditor()}
      </div>
    </div>);
  }
}
