import React from 'react';
import PropTypes from 'prop-types';
import { Button } from '@deskpro/react-components';

export default class EditModal extends React.Component {
  static propTypes = {
    closeModal: PropTypes.func,
    value:      PropTypes.string,
    onChange:   PropTypes.func,
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
        callback(obj) {
          self.redactor = obj;
        }
      });
    }
  }

  save = () => {
    const content = this.redactor.getCode();
    this.props.onChange(content);
    this.props.closeModal();
  };

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
        <div>
          <Button type="primary" size="large" onClick={this.save}>
            Save
          </Button>
          <Button type="secondary" size="large" onClick={this.props.closeModal}>
            Cancel
          </Button>
        </div>
      </div>
    );
  }
}
