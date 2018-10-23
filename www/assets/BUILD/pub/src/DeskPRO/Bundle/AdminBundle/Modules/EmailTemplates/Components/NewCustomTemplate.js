import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Input, Field, Select } from 'DeskPRO/Component/Semantic/Form';
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
      name:         '',
      baseTemplate: 'SendmailBundle:emails_common:blank.html.twig',
      errors:       null
    };
  }

  handleClose = () => {
    this.props.close();
  };

  handleAddTemplate = () => {
    if (!this.state.name || this.errors) {
      return;
    }
    this.props.addTemplate(this.state.name, this.state.baseTemplate)
    .then(() => {
      this.props.close();
    });
  };

  handleBaseTemplate = (value) => {
    this.setState({
      baseTemplate: value
    });
  };

  handleName = (value) => {
    this.setState({
      name: value
    });
    this.validateName(value);
  };

  validateName = (value) => {
    if (!value.match(/^[a-z0-9-_.]+$/)) {
      this.setState({
        errors: {
          fields: {
            name: {
              errors: [
                {
                  message: 'Invalid name'
                }
              ]
            }
          }
        }
      });
    } else {
      this.setState({
        errors: null
      });
    }
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
        <Field field="name" errors={this.state.errors}>
          <label htmlFor="template_name">Template name: </label><br />
          <Input id="template_name" className="ui input" onChange={this.handleName} />.html<br />
        </Field>
        <span className="help-block">
          Enter a file name for your email template. Valid characters are lowercase letters, numbers, hyphens, periods and underscores.
        </span><br />
        <label htmlFor="base_template">Base template: </label><br />
        <Select options={templates} value={this.state.baseTemplate} onChange={this.handleBaseTemplate} filter />< br />< br />
        <Button
          onClick={this.handleAddTemplate}
          className={classNames({ loading: this.props.addingNewTemplate, disabled: this.state.errors })}
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
