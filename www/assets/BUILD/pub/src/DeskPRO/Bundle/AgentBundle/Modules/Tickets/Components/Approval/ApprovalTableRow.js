import React from 'react';
import PropTypes from 'prop-types';
import {FormattedMessage, injectIntl} from 'react-intl';
import moment from 'moment';
import { Icon, Button } from '@deskpro/react-components';
import { faTimes, faCheck, faClock } from '@fortawesome/free-solid-svg-icons';

@injectIntl
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
      message: 'Approved'
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
      message: 'Rejected'
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
        vote_type: vote.get('vote_type')
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
                <Button size="small" loading={this.state.saving} onClick={() => this.acceptApprovalRequest(approval.id)}>Accept</Button>
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
          <td width="10">&nbsp;</td>
          <td width="100">{vote.approver.name}</td>
          <td width="200">{vote.message}</td>
          <td width="100">&nbsp;</td>
          <td width="10">&nbsp;</td>
          <td width="10">&nbsp;</td>
          <td width="20">
            <FormattedMessage id={`agent.tickets.approvals.response.vote_type.${vote.vote_type}`} />
          </td>
          <td width="10">
            <Icon name={vote.vote_type === 'approve' ? faCheck : faTimes} />
          </td>
        </tr>
      );
    });

    const responsesStyle = {
      display: this.state.showResponses ? '' : 'none'
    };

    return [
      <tr key={`approval_${approval.id}_data`}>
        <td>
          <a href="#" onClick={this.toggleVotes}>{approval.id}</a>
        </td>
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
      </tr>,
      <tr key={`approval_${approval.id}_responses`} style={responsesStyle}>
        <td colSpan="8">
          <table>
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
