import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage, FormattedRelative } from 'react-intl';
import moment from 'moment';
import { Icon } from '@deskpro/react-components';
import { faTimes } from '@fortawesome/free-solid-svg-icons';

@injectIntl
class ApprovalTable extends React.Component {
  static propTypes = {
    approvals:             PropTypes.object,
    people:                PropTypes.object.isRequired,
    // cancelApprovalRequest: PropTypes.func,
    // acceptApprovalRequest: PropTypes.func,
    // rejectApprovalRequest: PropTypes.func,
  };

  render() {
    const titleStyle = {
      background: '#eee'
    };
    const colsStyle = {
      fontSize: '11px',
      padding: '1px 4px'
    };

    return (
      <table cellSpacing="0" cellPadding="0" width="100%" className="field-holders-table th-la sla-table">
        <tbody>
        <tr>
          <th colSpan="8" style={titleStyle}>
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
          <th width="10">&nbsp;</th>
        </tr>
        </tbody>
      </table>
    );
  }
}
export default ApprovalTable;
