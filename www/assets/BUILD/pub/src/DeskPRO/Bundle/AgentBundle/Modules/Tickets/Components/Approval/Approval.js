import React from 'react';
import PropTypes from 'prop-types';
import { Container, Button } from '@deskpro/react-components';
import { FormattedMessage } from 'react-intl';
import ApprovalTable from './ApprovalTable';
import ApprovalForm from './ApprovalForm';

export class Approval extends React.Component {

  static propTypes = {
    approvals:             PropTypes.object.isRequired,
    templates:             PropTypes.object.isRequired,
    ticketPerms:           PropTypes.object,
    createApprovalRequest: PropTypes.func,
    cancelApprovalRequest: PropTypes.func,
    acceptApprovalRequest: PropTypes.func,
    rejectApprovalRequest: PropTypes.func,
    getTemplateApprovers:  PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.state = {
      showForm: false
    };
  }

  createApprovalRequest = data => this.props.createApprovalRequest(data)
    .then(() => {
      this.setState({
        showForm: false
      });
    });

  render() {
    return (
      <Container className="approvals">
        {this.state.showForm ?
          (<div className="approvals-wrapper">
            <ApprovalForm
              templates={this.props.templates}
              ticketPerms={this.props.ticketPerms}
              getTemplateApprovers={this.props.getTemplateApprovers}
              createApprovalRequest={this.createApprovalRequest}
              cancelRequest={() => this.setState({ showForm: false })}
            />
          </div>)
          :
          (<div className="approvals-wrapper">
            <ApprovalTable
              approvals={this.props.approvals}
              ticketPerms={this.props.ticketPerms}
              cancelApprovalRequest={this.props.cancelApprovalRequest}
              acceptApprovalRequest={this.props.acceptApprovalRequest}
              rejectApprovalRequest={this.props.rejectApprovalRequest}
            />
            <div className="approval-form-buttons">
              <Button
                size="small"
                style={{ marginTop: '10px', marginBottom: '10px' }}
                onClick={() => this.setState({ showForm: true })}
              >
                <FormattedMessage id="agent.tickets.approvals.make_request" />
              </Button>
            </div>
          </div>)
        }
      </Container>
    );
  }
}
