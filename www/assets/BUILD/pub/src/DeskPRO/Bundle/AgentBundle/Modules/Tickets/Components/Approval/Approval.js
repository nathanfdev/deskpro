import React from "react";
import PropTypes from "prop-types";
import {Container, Button} from "@deskpro/react-components";
import { injectIntl, FormattedMessage } from 'react-intl';
import ApprovalTable from "./ApprovalTable";
import ApprovalForm from "./ApprovalForm";

@injectIntl
export class Approval extends React.Component {
  static propTypes = {
    approvals:      PropTypes.object.isRequired,
    people:         PropTypes.object.isRequired,
    ticketPerms:    PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      displayForm: false
    };
  }

  render() {
    const buttonStyle = {
      float: 'right',
      marginTop: '10px',
      marginBottom: '10px'
    };

    return (
      <Container className="approval">
        {this.state.displayForm ?
          <ApprovalForm
            people={this.props.people}
            ticketPerms={this.props.ticketPerms}
          />
          :
          <ApprovalTable
            people={this.props.people}
            approvals={this.props.approvals}
          />
        }
        <Button size="m" style={buttonStyle} className="dp-btn" onClick={this.displayForm}>
          <FormattedMessage id="agent.tickets.approvals.make_request" />
        </Button>
      </Container>
    );
  }
}
