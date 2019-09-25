import React from "react";
import PropTypes from "prop-types";
import {Container, Button} from "@deskpro/react-components";
import {FormattedMessage} from "react-intl";
import ApprovalTable from "./ApprovalTable";
import ApprovalForm from "./ApprovalForm";

export class Approval extends React.Component {
  static propTypes = {
    approvals:             PropTypes.object.isRequired,
    templates:             PropTypes.object.isRequired,
    ticketPerms:           PropTypes.object,
    dispatch:              PropTypes.func,
    createApprovalRequest: PropTypes.func,
    cancelApprovalRequest: PropTypes.func,
    acceptApprovalRequest: PropTypes.func,
    rejectApprovalRequest: PropTypes.func,
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
    const styles = {
      button: {
        float: 'right',
        marginTop: '10px',
        marginBottom: '10px'
      }
    };

    const button = (
      <Button
        size="m"
        style={styles.button}
        className="dp-btn"
        onClick={() => this.setState({ showForm: !this.state.showForm })}
      >
        <FormattedMessage id={
          this.state.showForm
            ? 'agent.tickets.approvals.cancel_make_request'
            : 'agent.tickets.approvals.make_request'
        } />
      </Button>
    );

    return (
      <Container className="approvals">
        {this.state.showForm ?
          (<div>
            <ApprovalForm
              templates={this.props.templates}
              ticketPerms={this.props.ticketPerms}
              getPeople={this.props.getPeople}
              createApprovalRequest={this.createApprovalRequest}
            />
            {button}
          </div>)
          :
          (<div>
            <ApprovalTable
              approvals={this.props.approvals}
              ticketPerms={this.props.ticketPerms}
              cancelApprovalRequest={this.props.cancelApprovalRequest}
              acceptApprovalRequest={this.props.acceptApprovalRequest}
              rejectApprovalRequest={this.props.rejectApprovalRequest}
            />
            {button}
          </div>)
        }
      </Container>
    );
  }
}
