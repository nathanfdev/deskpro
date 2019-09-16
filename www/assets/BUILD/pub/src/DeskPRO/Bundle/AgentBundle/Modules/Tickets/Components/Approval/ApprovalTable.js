import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import {connect} from "react-redux";
import { meSelector } from '../../../../../AppBundle/Modules/RecordsStore/Shortcuts/me';
import ApprovalTableRow from "./ApprovalTableRow";

@connect(state => ({
  me: meSelector(state),
}))
@injectIntl
class ApprovalTable extends React.Component {
  static propTypes = {
    approvals:             PropTypes.object,
    getPeople:             PropTypes.func,
    ticketPerms:           PropTypes.object,
    cancelApprovalRequest: PropTypes.func,
    acceptApprovalRequest: PropTypes.func,
    rejectApprovalRequest: PropTypes.func,
  };

  constructor(props) {
    super(props);
  }

  render() {
    const titleStyle = {
      background: '#eee'
    };
    const colsStyle = {
      fontSize: '11px',
      padding: '1px 4px'
    };

    const approvalsList = this.props.approvals.toArray().map(approval => {

      return (
        <ApprovalTableRow
          key={`approval_${approval.get('id')}_row`}
          me={this.props.me}
          approval={approval}
          ticketPerms={this.props.ticketPerms}
          cancelApprovalRequest={this.props.cancelApprovalRequest}
          acceptApprovalRequest={this.props.acceptApprovalRequest}
          rejectApprovalRequest={this.props.rejectApprovalRequest}
        />
      )
    });

    return (
      <table cellSpacing="0" cellPadding="0" width="100%" className="field-holders-table th-la sla-table">
        <tbody>
          <tr>
            <th colSpan="8" style={titleStyle}>
              <FormattedMessage id="agent.tickets.approvals.title" />
            </th>
          </tr>
          <tr className="linked-head-title">
            <th width="10" style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.id" />
            </th>
            <th width="100" style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.name" />
            </th>
            <th width="200" style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.description" />
            </th>
            <th width="100" style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.approvers" />
            </th>
            <th width="10" style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.success" />
            </th>
            <th width="10" style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.failure" />
            </th>
            <th  width="20" style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.status" />
            </th>
            <th width="10">&nbsp;</th>
          </tr>
          {approvalsList}
        </tbody>
      </table>
    );
  }
}
export default ApprovalTable;
