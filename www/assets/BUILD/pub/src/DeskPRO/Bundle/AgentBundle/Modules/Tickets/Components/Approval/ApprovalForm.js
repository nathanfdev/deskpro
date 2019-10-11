import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import { Form, Label, Button, Select } from '@deskpro/react-components';

@injectIntl
class ApprovalForm extends React.Component {

  static propTypes = {
    templates:             PropTypes.object.isRequired,
    intl:                  PropTypes.object,
    getPeople:             PropTypes.func,
    createApprovalRequest: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      // data
      template: {
        canChoose: null
      },
      people:    [],
      approvers: [],
      // form
      errors:    [],
      saving:    false,
    };
  }

  getTemplatesDropdowntOptions = () => {
    const { templates } = this.props;

    return templates.toArray().map(template => ({
      value: template.get('id'),
      label: template.get('name')
    }));
  };

  addApprover = (choice) => {
    const { approvers } = this.state;
    approvers.push(choice);

    this.setState({ approvers });
  };

  removeApprover = (id) => {
    const { approvers } = this.state;
    const approver = approvers.filter(opt => opt.value === id)[0];

    if (approver) {
      const index = approvers.indexOf(approver);
      if (index !== -1) {
        approvers.splice(index, 1);
      }

      this.setState({ approvers });
    }
  };

  handleTemplateChange = (value) => {
    // get templates from props
    const { templates } = this.props;

    // get selected template
    const template = templates.toArray().find(t => t.get('id') === value.value);

    let criteria = {};
    if (template.get('can_choose_approvers')) {
      const tplCriteria = template.get('approver_selection_criteria');
      criteria = {
        all_agents:            tplCriteria.get('can_select_from_all_agents'),
        organization_managers: tplCriteria.get('can_select_organization_managers'),
        ticket_user:           tplCriteria.get('can_select_ticket_user'),
        people:                tplCriteria.get('select_from_people').toArray().map(id => id),
        number_of_approvers:   tplCriteria.get('min_number_of_approvers')
      };
    } else {
      const tplCriteria = template.get('selected_approvers');
      criteria = {
        all_agents:            tplCriteria.get('has_all_agents'),
        organization_managers: tplCriteria.get('has_organization_managers'),
        ticket_user:           tplCriteria.get('has_ticket_user'),
        people:                tplCriteria.get('people').toArray().map(id => id),
        number_of_approvers:   tplCriteria.get('people').toArray().length
      };
    }

    // update state
    this.setState({
      template: {
        criteria,
        id:          template.get('id'),
        canChoose:   template.get('can_choose_approvers'),
        toApprove:   template.get('required_approvals'),
        toReject:    template.get('required_rejections'),
        canViewSubj: template.get('can_approvers_view_subject')
      }
    });

    // concat list of possible approvers and fetch people by ids
    if (criteria.people.length > 0) {
      this.props.getPeople(criteria.people).then((res) => {
        const people = res.data.map(person => ({
          // select
          value:   person.id,
          label:   person.display_name,
          // data
          id:      person.id,
          avatar:  person.gravatar_url,
          name:    person.display_name,
          isAgent: person.is_agent
        }));

        if (!template.get('can_choose_approvers')) {
          this.setState({ people });
        }

        // update state
        this.setState({ people });
      });
    }


    // if agent can choose approvers
    if (template.get('can_choose_approvers')) {
      // update state
      this.setState({
        approvers: [],
      });
    }
  };

  createApprovalRequest = (event, values) => {
    if (this.state.saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const errors = [];

    // prepare submit data
    const data = {
      ...values,
      template:  this.state.template.id,
      approvers: this.state.approvers.map(approver => approver.id)
    };

    if (this.state.template.canChoose) {
      const totalApprovers = parseInt(this.state.template.criteria.number_of_approvers, 10);
      if (data.approvers.length < totalApprovers) {
        errors.push(
          `List of approver must be supplied. Please choose ${this.state.template.criteria.number_of_approvers} approvers`
        );
      }
    }

    if (errors.length < 1) {
      this.props.createApprovalRequest(data)
        .catch((error) => {
          switch (error.data.status) {
            case 403:
              errors.push('You do not have permission to create approval requests');
              break;
            case 500:
              errors.push('An error has occurred while processing request on server');
              break;
            default:
              errors.push('An error has occurred. Please try again later or call for assistance');
              break;
          }
          this.setState({
            errors,
            saving: false
          });
        });
    } else {
      this.setState({
        errors,
        saving: false
      });
    }
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
    // prepare approvers dropdown, list and info
    let approversSelect = '';
    let approversList   = '';
    let approversInfo   = '';
    let maxApprovers    = 0;

    if (this.state.template.canChoose === true) {
      maxApprovers = parseInt(this.state.template.criteria.number_of_approvers, 10);
      approversInfo = this.state.approvers.length < maxApprovers
        ? (<FormattedMessage
          id="agent.tickets.approvals.select_approvers"
          values={{ n: maxApprovers - this.state.approvers.length }}
        />)
        : (<FormattedMessage
          id="agent.tickets.approvals.approve_condition"
          values={{ m: maxApprovers, reqa: this.state.template.toApprove, reqr: this.state.template.toReject }}
        />);

      approversSelect  = this.state.approvers.length < maxApprovers ? (
        <div className="col" style={{ maxWidth: '230px' }}>
          <Label>
            <FormattedMessage id="agent.tickets.approvals.approvers" />
            <span className="info">( {approversInfo} )</span>
          </Label>
          <Select
            options={this.state.people.filter(p => this.state.approvers.indexOf(p) === -1)}
            onChange={this.addApprover}
          />
        </div>
      ) : '';

      approversList = (
        <ul className="approvers-list">
          {this.state.approvers.map((approver) => {
            const avatarStyle = {
              backgroundImage: `url(${approver.avatar})`,
              backgroundSize:  'contain'
            };

            return (
              <li key={approver.id}>
                <a className="as-popover dp-btn dp-btn-small">
                  <span className="text" style={avatarStyle}>{approver.name}</span>
                  <span
                    className="remove-row-trigger nohide-edit"
                    onClick={(ev) => { ev.preventDefault(); this.removeApprover(approver.id); }}
                  >
                    <i className="fas fa-times" />
                  </span>
                </a>
              </li>
            );
          })}
        </ul>
      );
    } else if (this.state.template.canChoose === false) {
      approversInfo = <FormattedMessage id="agent.tickets.approvals.approvers_set_from_template" />;
    }

    return (
      <div>
        <Form className="request-form" onSubmit={this.createApprovalRequest}>
          <div className="row">
            <div className="col" style={{ maxWidth: '230px' }}>
              <Label>
                <FormattedMessage id="agent.tickets.approvals.template" />
              </Label>
              <Select
                className="select"
                options={this.getTemplatesDropdowntOptions()}
                placeholder={this.props.intl.formatMessage({ id: 'agent.tickets.approvals.choose_template' })}
                onChange={this.handleTemplateChange}
              />
            </div>
            {approversSelect}
            <div className="col">
              {maxApprovers !== 0 && this.state.approvers.length === maxApprovers
                ? <Label>
                  <FormattedMessage id="agent.tickets.approvals.approvers" />
                  <span className="info">( {approversInfo} )</span>
                </Label>
                : <Label>&nbsp;</Label>
              }
              <div>{approversList}</div>
            </div>
          </div>
          <div className="row">
            <div className="col">
              <Label>
                <FormattedMessage id="agent.tickets.approvals.description" />
              </Label>
              <textarea
                name="description"
                className="form-control"
                rows="4"
              />
            </div>
          </div>
          <div>
            <Button
              style={{ float: 'right', marginTop: '10px', marginBottom: '10px', marginLeft: '10px' }}
              size="small"
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
