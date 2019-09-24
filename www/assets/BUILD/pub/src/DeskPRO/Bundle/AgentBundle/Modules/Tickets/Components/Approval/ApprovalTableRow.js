import React from 'react';
import PropTypes from 'prop-types';
import {FormattedMessage} from 'react-intl';
import moment from 'moment';
import { Icon, Button } from '@deskpro/react-components';
import { faTimes, faCheck, faClock, faCaretRight } from '@fortawesome/free-solid-svg-icons';
import {faEnvelope} from "@fortawesome/free-regular-svg-icons";

class ApprovalTableRow extends React.Component {
  static propTypes = {
    me:                    PropTypes.object,
    approval:              PropTypes.object.isRequired,
    ticketPerms:           PropTypes.object,
    cancelApprovalRequest: PropTypes.func.isRequired,
    acceptApprovalRequest: PropTypes.func.isRequired,
    rejectApprovalRequest: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      showResponses: false,
      saving: false,
    };
  }

  cancelApprovalRequest = id => {
    this.setState({
      saving: true
    });

    this.props.cancelApprovalRequest(id)
      .then(() => {
        this.setState({
          saving: false
        });
      })
  };

  acceptApprovalRequest = id => {
    this.setState({
      saving: true
    });

    // @fixme
    const data = {
      message: ''
    };

    this.props.acceptApprovalRequest(id, data)
      .then(() => {
        this.setState({
          saving: false
        });
      })
  };

  rejectApprovalRequest = id => {
    this.setState({
      saving: true
    });

    // @fixme
    const data = {
      message: ''
    };

    this.props.rejectApprovalRequest(id, data)
      .then(() => {
        this.setState({
          saving: false
        });
      })
  };

  toggleVotes = () => {
    this.setState({
      showResponses: !this.state.showResponses
    });
  };

  render() {

    const approvers = this.props.approval.get('people').toArray().map(approver => ({
      id:     approver.get('id'),
      name:   approver.get('display_name'),
      avatar: approver.get('gravatar_url')
    }));

    const approval = {
      id:                  this.props.approval.get('id'),
      name:                this.props.approval.get('name'),
      description:         this.props.approval.get('description'),
      status:              this.props.approval.get('status'),
      required_approvals:  this.props.approval.get('required_approvals'),
      required_rejections: this.props.approval.get('required_rejections'),
      creator:             parseInt(this.props.approval.get('created_by')),
      approvers:           approvers,
      approvers_pending:   this.props.approval.get('approvers_pending_response').toArray(),
      votes:               this.props.approval.get('votes').toArray().map(vote => ({
        id:        vote.get('id'),
        approver:  approvers.find(obj => obj.id === parseInt(vote.get('approver'))),
        message:   vote.get('message'),
        vote_type: vote.get('vote_type'),
        created_at: moment(vote.get('created_at')).format(),
      }))
    };

    let controls = '';
    if (approval.status === 'pending') {
      if (this.props.me) {
        const meId = parseInt(this.props.me.get('id'));
        const iAmApprover = approval.approvers.findIndex(approver => meId === approver.id) > -1;

        if (meId === approval.creator) {
          controls = <Button size="small" loading={this.state.saving} onClick={() => this.cancelApprovalRequest(approval.id)}>Cancel</Button>
        } else if (iAmApprover) {
          if (approval.approvers_pending.includes(meId)) {
            controls = (
              <div>
                <Button style={{ marginRight: '3px' }} size="small" loading={this.state.saving} onClick={() => this.acceptApprovalRequest(approval.id)}>Accept</Button>
                <Button size="small" loading={this.state.saving} onClick={() => this.rejectApprovalRequest(approval.id)}>Reject</Button>
              </div>
            );
          }
        }
      }
    }

    const votesList = approval.votes.map(vote => {
      return (
        <tr key={`approval_${this.props.approval.get('id')}_vote_${vote.id}`}>
          <td>&nbsp;</td>
          <td>{vote.approver.name}</td>
          <td>{vote.message}</td>
          <td>{vote.created_at}</td>
          <td>
            <Icon name={vote.vote_type === 'approve' ? faCheck : faTimes} />
          </td>
          <td>
            <FormattedMessage id={`agent.tickets.approvals.response.vote_type.${vote.vote_type}`} />
          </td>
          <td>&nbsp;</td>
        </tr>
      );
    });

    const responsesStyle = {
      display: this.state.showResponses ? '' : 'none'
    };

    const approversList = approval.approvers.length <= 2
      ? approval.approvers.map(approver => approver.name).join(', ')
      : approval.approvers.slice(0, 1).map(approver => approver.name) + ` + ${approval.approvers.length - 1} more`;

    const approvalStatusIcon = <Icon
      name={
        approval.status === 'pending'
          ? faClock
          : approval.status === 'approved'
          ? faCheck : faTimes
      }
    />;

    return [
      <tr key={`approval_${approval.id}_data`}>
        <td>
          <Icon name={faCaretRight} style={{ marginRight: '5px' }} />
          <a href="#" onClick={this.toggleVotes}>{approval.id}</a>
        </td>
        <td>{approval.name}</td>
        <td>{approval.description}</td>
        <td>{approversList}</td>
        <td>{approval.required_approvals}</td>
        <td>{approval.required_rejections}</td>
        <td>
          <FormattedMessage
            id={`agent.tickets.approvals.status_name.${approval.status}`}
          />
        </td>
        <td style={{ textAlign: 'center' }}>
          {controls}
        </td>
        <td>
          {approvalStatusIcon}
        </td>
      </tr>,
      <tr key={`approval_${approval.id}_responses`} style={responsesStyle}>
        <td colSpan="9" style={{ padding: '10px' }}>
          <table style={{ tableLayout: 'fixed', width: '100%' }}>
            <colgroup>
              <col style={{ width: '40px' }} />
              <col style={{ width: '100px' }} />
              <col style={{ width: '20%' }} />
              <col style={{ width: '20%' }} />
              <col style={{ width: '100px' }} />
              <col style={{ width: '70px' }} />
              <col style={{ width: '30px' }} />
            </colgroup>
            <tbody>
              {votesList}
            </tbody>
          </table>
        </td>
      </tr>
    ];

  }
}
export default ApprovalTableRow;
