import React, { PropTypes } from 'react';
import classNames from 'classnames';
import CodeMirror from './CodeMirror';

class Editor extends React.Component {
  static propTypes = {
    disabled:              PropTypes.bool,
    subject:               PropTypes.string,
    body:                  PropTypes.string,
    changeTemplateBody:    PropTypes.func,
    changeTemplateSubject: PropTypes.func,
    phrases:               PropTypes.object,
  };
  static defaultProps = {
    disabled: false,
    changeTemplateBody() {},
    changeTemplateSubject() {},
  };

  onClickVariable = (e) => {
    console.log(e);
    console.log(e.target);
  };

  findPhrase = key => this.props.phrases.getIn(['all', 'phrases', key], key);

  addMarks = (cm, change) => {
    if (change.text.length) {
      let text = [];
      const doc = cm.getDoc();
      const from = change.from;
      switch (change.origin) {
        case '+input':
          text = [doc.getLine(from.line)];
          break;
        case 'undo':
          return null;
        case 'setValue':
        default:
          text = change.text;
          break;
      }
      text.forEach((content, line) => {
        let match;
        let re;
        re = /{{\s*([a-z0-9_.]+)\s*}}/g;
        match = re.exec(content);
        while (match !== null) {
          const element = document.createElement('span');
          element.innerHTML = match[1];
          element.className = 'twig-variable';
          element.onclick = this.onClickVariable;
          doc.markText({
            line: line + from.line,
            ch:   match.index
          }, {
            line: line + from.line,
            ch:   match.index + match[0].length
          }, {
            atomic:       true,
            replacedWith: element,
          });
          match = re.exec(content);
        }
        re = /{{\s*phrase\('([^)]+)'\)\s*}}/g;
        match = re.exec(content);
        while (match !== null) {
          const element = document.createElement('span');
          element.innerHTML = this.findPhrase(match[1]);
          element.className = 'twig-phrase';
          doc.markText({
            line: line + from.line,
            ch:   match.index
          }, {
            line: line + from.line,
            ch:   match.index + match[0].length
          }, {
            atomic:       true,
            replacedWith: element,
          });
          match = re.exec(content);
        }
        re = /{%\s*include\s*'([^)]+)'\s*%}/g;
        match = re.exec(content);
        while (match !== null) {
          const element = document.createElement('span');
          element.innerHTML = match[1].replace(/^SendmailBundle:/, '');
          element.className = 'twig-include';
          doc.markText({
            line: line + from.line,
            ch:   match.index
          }, {
            line: line + from.line,
            ch:   match.index + match[0].length
          }, {
            atomic:       true,
            replacedWith: element,
          });
          match = re.exec(content);
        }
      });
      setTimeout(() => {
        cm.refresh();
      }, 1);
    }
    return true;
  };

  handleSubjectChange = (cm, change) => {
    this.props.changeTemplateSubject(cm.getValue());
    this.addMarks(cm, change);
  };

  handleBodyChange = (cm, change) => {
    this.props.changeTemplateBody(cm.getValue());
    this.addMarks(cm, change);
  };

  render() {
    const { disabled, subject, body } = this.props;
    return (
      <div className={classNames('dp-code-editor', { disabled })}>
        <div className={classNames('ui dimmer inverted', { active: disabled })}>
          <div className="ui big loader text">Please select a template to edit</div>
        </div>
        <div className="subject">
          Email subject:
          <CodeMirror
            value={subject}
            onChange={this.handleSubjectChange}
          />
        </div>
        <div className="body">
          Email:
          <CodeMirror
            value={body}
            ref={(c) => { this.bodyEditor = c; }}
            onChange={this.handleBodyChange}
          />
        </div>
      </div>
    );
  }
}
export default Editor;
