import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import CM from 'codemirror';
import MarkdownIt from 'markdown-it';
import MarkdownItContainer from 'markdown-it-container';
import toMarkdown from 'to-markdown';
import hljs from 'highlight.js';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import 'codemirror/mode/gfm/gfm';
import 'codemirror/addon/edit/continuelist';
import LinkMenu from './LinkMenu';
import MarkdownItTabs from './tabs';
import { getCursorState, applyFormat } from './format';
import * as Icons from './Icons';

class MarkdownEditor extends React.Component {
  static propTypes = {
    onChange:         PropTypes.func,
    onAddFile:        PropTypes.func,
    loadRemoteImages: PropTypes.func,
    options:          PropTypes.object,
    name:             PropTypes.string,
    value:            PropTypes.string,
  };
  static defaultProps = {
    onChange() {},
    onAddFile() {},
    loadRemoteImages() {},
  };

  static renderIcon(icon) {
    return <span className="MDEditor_toolbarButton_icon">{icon}</span>;
  }

  static prerenderHtml(html) {
    return html
      .replace(/\{\{ img\(([^)]+)\) }}/g, '/file.php/$1')
      .replace(/\{\{ content\(([^)]+)\) }}/g, '#')
      .replace(/\{\{\s*content_link\(([^,]+),([^),]+)(,[^)]+)?\)\s*}}/g, '<a href="#">$1:$2</a>');
  }

  static toMarkdown(html) {
    const markdown = toMarkdown(html, {
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
    return markdown.replace(/\(\/file\.php\/([^)]+)\)/g, '({{ img($1) }})');
  }

  constructor(props) {
    super(props);
    this.md = new MarkdownIt({
      html:    false,
      linkify: true
    });
    this.md.linkify
      .set({ fuzzyLink: false })
      .add('www.', {
        validate(text, pos, self) {
          if (self.re.link_fuzzy.test(text)) {
            const sub = pos > 4 ? 5 : 4;
            return text.match(self.re.link_fuzzy)[0].length - sub;
          }
          return 0;
        },
        normalize(match) {
          match.url = `http://${match.url}`;
        }
      });
    this.md
      .use(MarkdownItContainer, 'warning', {
        render(tokens, idx) {
          return tokens[idx].nesting === 1
            ? '<div class="block warning">\n'
            : '</div>\n';
        }
      })
      .use(MarkdownItContainer, 'error', {
        render(tokens, idx) {
          return tokens[idx].nesting === 1
            ? '<div class="block error">\n'
            : '</div>\n';
        }
      })
      .use(MarkdownItContainer, 'info', {
        render(tokens, idx) {
          return tokens[idx].nesting === 1
            ? '<div class="block info">\n'
            : '</div>\n';
        }
      })
      .use(MarkdownItTabs, {})
    ;
    this.state = {
      isFocused: false,
      cs:        {},
      html:      this.renderHtml(props.value)
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

    const that = this;
    setTimeout(() => {
      that.codeMirror.refresh();
    }, 1);
  }

  componentWillReceiveProps(nextProps) {
    if (this.codeMirror && this.currentCodemirrorValue !== nextProps.value) {
      this.codeMirror.setValue(nextProps.value);

      const that = this;
      setTimeout(() => {
        that.codeMirror.refresh();
      }, 1);
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
      mode:           'gfm',
      lineNumbers:    false,
      theme:          'default',
      lineWrapping:   true,
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
    const markdown = MarkdownEditor.toMarkdown(html);
    this.codeMirror.replaceSelection(markdown);
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
    this.props.loadRemoteImages(images, blobs => this.replaceRemoteImages(blobs));
  };

  replaceRemoteImages = (blobs) => {
    let result = this.props.value;
    blobs.forEach((element) => {
      const image = element.match.replace(
        element.source,
        `{{ img(${element.blob.blob_auth}/${element.blob.filename}) }}`
      );
      result = result.replace(element.match, image);
    });
    const html = this.renderHtml(result);
    this.props.onChange(result, html);
  };

  appendImage = (data) => {
    let code;
    if (data.content_type.match(/^image/)) {
      code = `![${data.filename}]({{ img(${data.blob_auth}/${data.filename}) }})`;
    } else {
      code = `[${data.filename}](${data.download_url})`;
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

  openLinkDialog = () => {
    this.linkMenu.togglePopup();
  };

  insertLink = (link) => {
    this.codeMirror.replaceSelection(link);
    this.linkMenu.closePopup();
  };

  codemirrorValueChanged = (doc) => {
    const newValue = doc.getValue();
    this.currentCodemirrorValue = newValue;
    const html = this.renderHtml(newValue);
    this.setState({ html });
    this.props.onChange(newValue, html);
  };

  toggleFormat(formatKey, e) {
    e.preventDefault();
    applyFormat(this.codeMirror, formatKey);
  }

  renderHtml = (markdown) => {
    let html = this.md.render(markdown)
      .replace(/!\[([^\]]+)]\((\{\{.+}})\)/g, (m, alt, src) => (`<img src="${src}" alt="${alt}" />`))
      .replace(/\[([^\]]+)]\((\{\{.+}})\)/g, (m, content, href) => (`<a href="${href}">${content}</a>`));
    const container = document.createElement('div');
    container.innerHTML = html;

    const nodes = container.querySelectorAll('code');
    hljs.configure({
      languages: [],
    });
    if (nodes.length > 0) {
      for (let i = 0; i < nodes.length; i += 1) {
        hljs.highlightBlock(nodes[i]);
      }
      html = container.innerHTML;
    }
    return html;
  };

  renderButton(formatKey, label, action) {
    const onClickAction = (!action) ? this.toggleFormat.bind(this, formatKey) : action;

    const isTextIcon = (['h1', 'h2', 'h3', 'code'].indexOf(formatKey) !== -1);
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
        <PopUp
          positionMy="right top"
          positionAt="right bottom"
          elementId="markdown-add-link"
          style={{ display: 'inline-block' }}
          zIndex={99999}
          content={<LinkMenu insertLink={this.insertLink} />}
          ref={(c) => { this.linkMenu = c; }}
          autoOpen={false}
        >
          {this.renderButton('link', 'l', this.openLinkDialog)}
        </PopUp>
        {this.renderButton('oList', 'ol')}
        {this.renderButton('uList', 'ul')}
        {this.renderButton('quote', 'q')}
        {this.renderButton('info', 'i')}
        {this.renderButton('warning', '!')}
        {this.renderButton('code', '>')}
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
            name={this.props.name}
            defaultValue={this.props.value}
            autoComplete="off"
          />
        </div>
        <h3>Preview</h3>
        <div
          className="preview guides"
          dangerouslySetInnerHTML={{ __html: MarkdownEditor.prerenderHtml(this.state.html) }}
        />
      </div>
    );
  }
}

export default MarkdownEditor;
