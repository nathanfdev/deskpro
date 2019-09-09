import React from "react";
import PropTypes from "prop-types";
import {injectIntl, FormattedMessage, intlShape} from "react-intl";
import {Form, Label, Button, Select, CustomSelect, Textarea} from "@deskpro/react-components";
import {template} from "handlebars/lib/handlebars/runtime";

@injectIntl
export class ApprovalForm extends React.Component {
  static propTypes = {
    people:      PropTypes.object.isRequired,
    templates:   PropTypes.object.isRequired,
    ticketPerms: PropTypes.object,
    intl:        intlShape.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      approvers: [],
      errors:    [],
      saving:    false,
    };
  }

  createApprovalRequest = () => {
    if (this.state.saving) {
      return false;
    }

    this.setState({
      saving: true
    });

    let errors = [];
  };

  defineApproversFromRequest = (value) => {
    const { templates } = this.props;
    const template = templates.toArray().find(template => template.get('id') === value.value).toArray();

    if (template.get('can_choose_approvers')) {
      // show approvers dropdown
    }
  };

  getTemplateOptions = () => {
    const { templates } = this.props;

    return templates.toArray().map(template => ({
      value: template.get('id'),
      label: template.get('name')
    }));
  };

  renderErrors = () => {
    if (this.state.errors.length === 0) {
      return null;
    }

    return (
      <div className="errors">
        <ul>
          {this.state.errors.map((error, index) => <li key={index}>{error}</li>)}
        </ul>
      </div>
    );
  };

  render() {
    const styles = {
      description: {
        minWidth: '100%'
      },
      templates: {
        maxWidth: '250px'
      }
    };

    return (
      <div>
        <Form onSubmit={(event, values) => { console.log(values); }}>
          <div className="row">
            <div className="col-md-6">
              <Label>
                <FormattedMessage id="agent.tickets.approvals.template" />
              </Label>
              <Select
                style={styles.templates}
                options={this.getTemplateOptions()}
                clearable={false}
                searchable={false}
                placeholder={this.props.intl.formatMessage({id: 'agent.tickets.approvals.choose_template'})}
                onChange={this.defineApproversFromRequest}
              />
            </div>
            <div className="col-md-6">
              <Label>
                <FormattedMessage id="agent.tickets.approvals.approvers" />
              </Label>
              {/*<Select options={this.getTemplateOptions()} />*/}
              {/*<CustomSelect></CustomSelect>*/}
            </div>
          </div>
          <div className="row">
            <div className="col-md-12">
              <Label>
                <FormattedMessage id="agent.tickets.approvals.description" />
              </Label>
              <Textarea className="form-control" style={styles.description} />
            </div>
          </div>
          <div>
            <Button
              size="medium"
              onClick={this.createApprovalRequest}
              loading={this.state.saving}
            >
              <FormattedMessage id="agent.general.create" />
            </Button>
          </div>
        </Form>
        {this.renderErrors()}
      </div>
    );
  }
}
export default ApprovalForm;
