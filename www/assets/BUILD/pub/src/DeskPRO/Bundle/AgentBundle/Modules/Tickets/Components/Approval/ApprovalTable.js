import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage, FormattedRelative } from 'react-intl';
import moment from 'moment';
import { Icon, Button } from '@deskpro/react-components';
import { faTimes, faCheck, faClock } from '@fortawesome/free-solid-svg-icons';
import {connect} from "react-redux";
import { meSelector } from '../../../../../AppBundle/Modules/RecordsStore/Shortcuts/me';

@connect(state => ({
  me: meSelector(state),
}))
class ApprovalTable extends React.Component {
  static propTypes = {
    approvals: PropTypes.object,
    getPeople: PropTypes.func,
    cancelApprovalRequest: PropTypes.func,
    acceptApprovalRequest: PropTypes.func,
    rejectApprovalRequest: PropTypes.func,
  };

  render() {
    const titleStyle = {
      background: '#eee'
    };
    const colsStyle = {
      fontSize: '11px',
      padding: '1px 4px'
    };

    const approvals = this.props.approvals.toArray().map(approval => {
      return {
        id:                  approval.get('id'),
        name:                approval.get('name'),
        description:         approval.get('description'),
        status:              approval.get('status'),
        required_approvals:  approval.get('required_approvals'),
        required_rejections: approval.get('required_rejections'),
        creator:             parseInt(approval.get('created_by')),
        approvers:           approval.get('approvers').toArray().map(approver => ({
          id:     approver.get('id'),
          name:   approver.get('display_name'),
          avatar: approver.get('gravatar_url')
        }))
      }
    });

    const approvalsList = approvals.map(approval => {

      let controls = '';
      if (approval.status === 'pending') {
        if (this.props.me) {
          const meId = parseInt(this.props.me.get('id'));
          const iAmApprover = approval.approvers.findIndex(approver => meId === approver.id) > -1;

          if (meId === approval.creator) {
            controls = <Button size="small" onClick={() => this.props.cancelApprovalRequest(approval.id)}>Cancel</Button>
          } else if (iAmApprover) {
            controls = (
              <div>
                <Button size="small" onClick={() => this.props.acceptApprovalRequest(approval.id)}>Accept</Button>
                <Button size="small" onClick={() => this.props.rejectApprovalRequest(approval.id)}>Reject</Button>
              </div>
            );
          }
        }
      } else {

      }


      return (
        <tr key={`approval_${approval.id}`}>
          <td>{approval.id}</td>
          <td>{approval.name}</td>
          <td>{approval.description}</td>
          <td>{approval.approvers.map(approver => approver.name).join(', ')}</td>
          <td>{approval.required_approvals}</td>
          <td>{approval.required_rejections}</td>
          <td>
            <FormattedMessage
              id={`agent.tickets.approvals.status_name.${approval.status}`}
            />
          </td>
          <td>
            {controls}
          </td>
        </tr>
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
          <th style={colsStyle}>
            <FormattedMessage id="agent.tickets.approvals.name" />
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
