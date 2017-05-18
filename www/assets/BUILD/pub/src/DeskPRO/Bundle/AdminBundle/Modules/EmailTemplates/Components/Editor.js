import React, { PropTypes } from 'react';
import classNames from 'classnames';
import CodeMirror from './CodeMirror';
import { PhraseWidget, TemplateWidget } from './Editor/';

class Editor extends React.Component {
  static propTypes = {
    disabled:               PropTypes.bool,
    subject:                PropTypes.string,
    body:                   PropTypes.string,
    changeTemplateBody:     PropTypes.func,
    changeTemplateSubject:  PropTypes.func,
    getPhraseTranslations:  PropTypes.func,
    loadTemplate:           PropTypes.func,
    resetTemplate:          PropTypes.func,
    savePhraseTranslations: PropTypes.func,
    setCurrentWidget:       PropTypes.func,
    setTemplateValue:       PropTypes.func,
    phrases:                PropTypes.object,
  };
  static defaultProps = {
    disabled: false,
    changeTemplateBody() {},
    changeTemplateSubject() {},
  };

  constructor(props) {
    super(props);
    this.widgets = [];
  }

  componentWillUnmount() {
    this.widgets.forEach((widget) => {
      widget.unmount();
    });
  }

  findPhrase = key => this.props.phrases.getIn(['all', 'phrases', key], key);

  addMarks = (cm, change, setCurrentWidget) => {
    if (change.text.length) {
      let text = [];
      const doc = cm.getDoc();
      const from = change.from;
      switch (change.origin) {
        case '+input':
        case 'popup':
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
        // Variable Widget is disabled at the moment
        // re = /{{\s*([a-z0-9_.]+)\s*}}/g;
        // match = re.exec(content);
        // while (match !== null) {
        //   widgets.push(new VariableWidget(
        //     cm,
        //     {
        //       line: line + from.line,
        //       ch:   match.index
        //     },
        //     match[0],
        //     match[1]
        //   ));
        //   match = re.exec(content);
        // }
        re = /{{\s*phrase\('([^)]+)'(,\s*{[^}]+})?\)\s*}}/g;
        match = re.exec(content);
        while (match !== null) {
          this.widgets.push(new PhraseWidget(
            cm,
            {
              line: line + from.line,
              ch:   match.index
            },
            match[0],
            this.findPhrase(match[1]),
            setCurrentWidget,
            this.props.getPhraseTranslations,
            this.props.savePhraseTranslations
          ));
          match = re.exec(content);
        }
        re = /{%\s*include\s*'([^)]+)'\s*%}/g;
        match = re.exec(content);
        while (match !== null) {
          this.widgets.push(new TemplateWidget(
            cm,
            {
              line: line + from.line,
              ch:   match.index
            },
            match[0],
            match[1],
            setCurrentWidget,
            this.props.loadTemplate,
            this.props.resetTemplate,
            this.props.setTemplateValue
          ));
          match = re.exec(content);
        }
      });
    }
    return true;
  };

  handleSubjectChange = (cm, change) => {
    this.props.changeTemplateSubject(cm.getValue());
    this.addMarks(cm, change, this.props.setCurrentWidget);
  };

  handleBodyChange = (cm, change) => {
    this.props.changeTemplateBody(cm.getValue());
    this.addMarks(cm, change, this.props.setCurrentWidget);
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
