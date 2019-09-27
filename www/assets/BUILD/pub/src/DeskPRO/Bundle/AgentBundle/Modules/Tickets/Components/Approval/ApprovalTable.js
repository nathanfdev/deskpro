import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import {connect} from "react-redux";
import { meSelector } from '../../../../../AppBundle/Modules/RecordsStore/Shortcuts/me';
import ApprovalTableRow from "./ApprovalTableRow";

@connect(state => ({
  me: meSelector(state),
}))
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

    let approvalsList = [];
    if (this.props.approvals.toArray().length > 0) {
      approvalsList = this.props.approvals.toArray().map(approval => {

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
    } else {
      approvalsList = (
        <tr>
          <td colSpan="9" style={{ textAlign: 'center' }}>
            <FormattedMessage id="agent.tickets.approvals.no_approvals" />
          </td>
        </tr>
      );
    }

    return (
      <table cellSpacing="0" cellPadding="0" style={{ tableLayout: 'fixed' }} className="field-holders-table th-la">
        <colgroup>
          <col style={{ width: '40px' }} />
          <col style={{ width: '100px' }} />
          <col style={{ width: '20%' }} />
          <col style={{ width: '20%' }} />
          <col style={{ width: '50px' }} />
          <col style={{ width: '50px' }} />
          <col style={{ width: '70px' }} />
          <col style={{ width: '140px' }} />
          <col style={{ width: '30px' }} />
        </colgroup>
        <tbody>
          <tr>
            <th colSpan="9" style={titleStyle}>
              <FormattedMessage id="agent.tickets.approvals.title" />
            </th>
          </tr>
          <tr className="linked-head-title">
            <th style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.id" />
            </th>
            <th style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.type" />
            </th>
            <th style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.description" />
            </th>
            <th style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.approvers" />
            </th>
            <th style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.success" />
            </th>
            <th style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.failure" />
            </th>
            <th style={colsStyle}>
              <FormattedMessage id="agent.tickets.approvals.status" />
            </th>
            <th style={colsStyle}>&nbsp;</th>
            <th style={colsStyle}>&nbsp;</th>
          </tr>
          {approvalsList}
        </tbody>
      </table>
    );
  }
}
export default ApprovalTable;
