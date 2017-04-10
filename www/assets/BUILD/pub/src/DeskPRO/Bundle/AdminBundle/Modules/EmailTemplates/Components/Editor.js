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
  };
  static defaultProps = {
    disabled: false,
    changeTemplateBody() {},
    changeTemplateSubject() {},
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
            onChange={this.props.changeTemplateSubject}
          />
        </div>
        <div className="body">
          Email:
          <CodeMirror
            value={body}
            onChange={this.props.changeTemplateBody}
          />
        </div>
      </div>
    );
  }
}
export default Editor;
