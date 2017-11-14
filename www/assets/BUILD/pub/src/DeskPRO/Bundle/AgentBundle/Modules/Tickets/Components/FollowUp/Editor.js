import React from 'react';
import PropTypes from 'prop-types';

export default class Editor extends React.Component {
  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func, // eslint-disable-line react/no-unused-prop-types
  };

  static defaultProps = {
    mode:  'reply',
    value: '',
  };

  componentDidMount() {
    if (typeof DeskPRO_Window !== 'undefined') { // eslint-disable-line camelcase
      const self = this;
      const buttons = [
        'bold', 'italic', '|',
        'formatting', 'fontcolor', '|',
        'alignment', 'unorderedlist', 'outdent', 'indent', '|',
        'table', 'image', 'link', 'horizontalrule', '|',
        'html'
      ];
      DeskPRO_Window.initRteAgentReply(this.textArea, { // eslint-disable-line no-undef
        defaultIsHtml: true,
        autoresize:    false,
        focus:         true,
        cleanup:       true,
        buttons,
        interval:      1,
        keyupCallback() {
          self.props.onChange(self.redactor.getCode());
        },
        callback(obj) {
          self.redactor = obj;
        }
      });
    }
  }

  render() {
    return (
      <div>
        <textarea
          id="reply_editor"
          cols="30"
          rows="10"
          defaultValue={this.props.value}
          ref={(c) => { this.textArea = c; }}
        />
      </div>
    );
  }
}
