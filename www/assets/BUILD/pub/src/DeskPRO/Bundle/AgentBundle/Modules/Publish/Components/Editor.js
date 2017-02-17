import React, { PropTypes } from 'react';
import { Button, ButtonGroup } from 'DeskPRO/Component/Semantic/Button';
import MarkdownEditor from 'DeskPRO/Component/Markdown/MarkdownEditor';
import 'froala-editor/js/froala_editor.pkgd.min';
import $ from 'jquery';
import FroalaEditor from 'react-froala-wysiwyg';
import toMarkdown from 'to-markdown';
import MarkdownIt from 'markdown-it';


export class EditorContainer extends React.Component {
  static propTypes = {
    value:          PropTypes.string,
    inputType:      PropTypes.string,
    updateHtml:     PropTypes.func,
    updateMarkdown: PropTypes.func,
    hideEditor:     PropTypes.func,
    save:           PropTypes.func
  };

  onCancel = () => {
    this.props.hideEditor();
  };

  render() {
    return (<Editor
      value={this.props.value}
      inputType={this.props.inputType}
      updateHtml={this.props.updateHtml}
      updateMarkdown={this.props.updateMarkdown}
      onCancel={this.onCancel}
      onSave={this.props.save}
    />);
  }
}
export class Editor extends React.Component {
  static propTypes = {
    value:      PropTypes.string,
    inputType:  PropTypes.string,
    updateHtml: PropTypes.func,
    onSave:     PropTypes.func,
    onCancel:   PropTypes.func
  };
  static defaultProps = {
    value:     '',
    inputType: 'rte',
    updateHtml() {},
    onSave() {},
    onCancel() {}
  };

  constructor(props) {
    super(props);
    const inputType = this.props.inputType ? this.props.inputType : 'rte';
    this.state = {
      html:     this.props.value,
      markdown: toMarkdown(this.props.value),
      inputType
    };
  }

  onCancel = () => {
    this.props.onCancel();
    this.setState({
      html:     this.props.value,
      markdown: toMarkdown(this.props.value)
    });
  };

  onSave = () => {
    let input;
    let html;
    if (this.state.inputType === 'rte') {
      this.props.updateHtml(this.state.html);
      input = this.state.html;
      html = input;
    } else {
      const md = new MarkdownIt({
        html:        false,
        linkify:     true,
        typographer: true
      });
      html = md.render(this.state.markdown);
      this.props.updateHtml(html);
      input = this.state.markdown;
    }
    this.props.onSave(html, input, this.state.inputType);
  };

  onChange = (event) => {
    this.setState({ markdown: event.target.value });
  };

  onContentChange = (content) => {
    this.setState({ html: content });
  };

  getEditor = () => {
    switch (this.state.inputType) {
      case 'markdown': {
        return (<MarkdownEditor value={this.state.markdown} onChange={this.onChange} />);
      }
      case 'rte':
      default: {
        const froalaConfig = {
          // toolbarInline: true,
          charCounterCount: false,
          enter:            $.FroalaEditor.ENTER_BR,
          key:              'qENARBFSTb1G1QJg1RA=='
        };

        if (window.DeskPRO_Window) {
          froalaConfig.events = {
            'froalaEditor.focus': () => { window.DeskPRO_Window.keyboardShortcuts.isPaused = true; },
            'froalaEditor.blur':  () => { window.DeskPRO_Window.keyboardShortcuts.isPaused = false; }
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
    this.setState({ inputType: type });
    console.log(type);
    if (type === 'markdown') {
      this.setState({ markdown: toMarkdown(this.state.html) });
    } else {
      const md = new MarkdownIt({
        html:        false,
        linkify:     true,
        typographer: true
      });
      this.setState({ html: md.render(this.state.markdown) });
    }
  };

  render() {
    return (<div className="publish-editor">
      <div className="header">
        <ButtonGroup onChange={this.changeMode} activeKey={this.state.inputType}>
          <Button key="rte">Classic</Button>
          <Button key="markdown">Markdown</Button>
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
