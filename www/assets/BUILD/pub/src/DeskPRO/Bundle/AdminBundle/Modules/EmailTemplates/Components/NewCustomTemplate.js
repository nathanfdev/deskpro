import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Input, Select } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';

class NewCustomTemplate extends React.Component {
  static propTypes = {
    opened:            PropTypes.bool,
    addingNewTemplate: PropTypes.bool,
    close:             PropTypes.func,
    addTemplate:       PropTypes.func,
  };
  static defaultProps = {
    opened: false,
    close() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      baseTemplate: ''
    };
  }

  handleClose = () => {
    this.props.close();
  };

  handleAddTemplate = () => {
    this.props.addTemplate(this.nameInput.input.value, this.state.baseTemplate)
    .then(() => {
      this.props.close();
    });
  };

  handleBaseTemplate = (value) => {
    this.setState({
      baseTemplate: value
    });
  };

  render() {
    if (!this.props.opened) {
      return null;
    }
    const templates = [
      {
        label: 'Blank',
        value: 'SendmailBundle:emails_common:blank.html.twig'
      },
      {
        label: 'New Ticket Auto-Response',
        value: 'SendmailBundle:emails_user:ticket_new_autoreply.html.twig'
      },
      {
        label: 'New Agent Reply',
        value: 'SendmailBundle:emails_user:ticket_new_by_agent.html.twig'
      },
      {
        label: 'Agent - New Ticket Notification',
        value: 'SendmailBundle:emails_agent:ticket_new.html.twig'
      },
      {
        label: 'Agent - New Reply',
        value: 'SendmailBundle:emails_agent:ticket_reply.html.twig'
      },
      {
        label: 'Agent - Ticket Updated Notification',
        value: 'SendmailBundle:emails_agent:ticket_update.html.twig'
      },
    ];
    return (
      <div className="new-custom-template">
        <h2>Create custom template</h2>
        <span onClick={this.handleClose} className="close"><i className="fa fa-times" /></span>
        <label htmlFor="template_name">Template name: </label><br />
        <Input id="template_name" className="ui input" ref={(c) => { this.nameInput = c; }} />.html<br />
        <span className="help-block">
          Enter a file name for your email template. Valid characters are letters, numbers, hyphens, periods and underscores.
        </span><br />
        <label htmlFor="base_template">Base template: </label><br />
        <Select options={templates} onChange={this.handleBaseTemplate} placeholder="Template" filter />< br />< br />
        <Button
          onClick={this.handleAddTemplate}
          className={classNames({ loading: this.props.addingNewTemplate })}
        >
          Submit
        </Button>
        <Button
          onClick={this.handleClose}
          className="basic"
        >
          Cancel
        </Button>
      </div>
    );
  }
}
export default NewCustomTemplate;
