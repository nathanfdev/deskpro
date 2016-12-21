import React, { PropTypes } from 'react';
import classNames from 'classnames';

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
      <div className="dp-code-editor">
        <div className={classNames('ui dimmer inverted', { active: disabled })}>
          <div className="ui big loader text">Please select a template to edit</div>
        </div>
        Email subject:
        <textarea
          className={classNames('email-subject', { disabled })}
          rows="2"
          value={subject}
          disabled={disabled}
          onChange={this.props.changeTemplateSubject}
        />
        Email:
        <textarea
          className={classNames('email-body', { disabled })}
          rows="20"
          value={body}
          disabled={disabled}
          onChange={e => this.props.changeTemplateBody(e.target.value)}
        />
      </div>
    );
  }
}
export default Editor;
