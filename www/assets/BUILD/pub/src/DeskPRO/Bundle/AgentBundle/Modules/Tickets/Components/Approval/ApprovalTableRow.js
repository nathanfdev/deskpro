import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import moment from 'moment';
import { Icon, Button } from '@deskpro/react-components';
import { faTimes, faCheck, faClock, faCaretRight } from '@fortawesome/free-solid-svg-icons';

class ApprovalTableRow extends React.Component {
  static propTypes = {
    me:                    PropTypes.object,
    approval:              PropTypes.object.isRequired,
    cancelApprovalRequest: PropTypes.func.isRequired,
    acceptApprovalRequest: PropTypes.func.isRequired,
    rejectApprovalRequest: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      showResponses: false,
      saving:        false,
    };
  }

  cancelApprovalRequest = (id) => {
    this.setState({
      saving: true
    });

    this.props.cancelApprovalRequest(id)
      .then(() => {
        this.setState({
          saving: false
        });
      });
  };

  acceptApprovalRequest = (id) => {
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
      });
  };

  rejectApprovalRequest = (id) => {
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
      });
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
      approvers,
      id:                  this.props.approval.get('id'),
      name:                this.props.approval.get('name'),
      description:         this.props.approval.get('description'),
      status:              this.props.approval.get('status'),
      required_approvals:  this.props.approval.get('required_approvals'),
      required_rejections: this.props.approval.get('required_rejections'),
      creator:             parseInt(this.props.approval.get('created_by'), 10),
      approvers_pending:   this.props.approval.get('approvers_pending_response').toArray(),
      votes:               this.props.approval.get('votes').toArray().map(vote => ({
        id:         vote.get('id'),
        approver:   approvers.find(obj => obj.id === parseInt(vote.get('approver'), 10)),
        message:    vote.get('message'),
        vote_type:  vote.get('vote_type'),
        created_at: moment(vote.get('created_at')).format('DD/MM/YYYY'),
      })),
      created_at:   moment(this.props.approval.get('created_at')).format('DD/MM/YYYY'),
      completed_at: moment(this.props.approval.get('completed_at')).format('DD/MM/YYYY'),
      cancelled_at: moment(this.props.approval.get('cancelled_at')).format('DD/MM/YYYY'),
    };

    let controls = '';
    if (approval.status === 'pending') {
      if (this.props.me) {
        const meId = parseInt(this.props.me.get('id'), 10);
        const iAmApprover = approval.approvers.findIndex(approver => meId === approver.id) > -1;

        if (meId === approval.creator) {
          if (iAmApprover && approval.approvers_pending.includes(meId)) {
            controls = (
              <div>
                <Button size="small" loading={this.state.saving} onClick={() => this.cancelApprovalRequest(approval.id)}>Cancel</Button>
                <Button style={{ marginRight: '3px' }} size="small" loading={this.state.saving} onClick={() => this.acceptApprovalRequest(approval.id)}>Accept</Button>
                <Button size="small" loading={this.state.saving} onClick={() => this.rejectApprovalRequest(approval.id)}>Reject</Button>
              </div>
            );
          } else {
            controls = (
              <div>
                <Button size="small" loading={this.state.saving} onClick={() => this.cancelApprovalRequest(approval.id)}>Cancel</Button>
              </div>
            );
          }
        } else {
          controls = (
            <div>
              <Button size="small" loading={this.state.saving} onClick={() => this.cancelApprovalRequest(approval.id)}>Cancel</Button>
            </div>
          );
        }
      }
    }

    let votesList = '';
    if (approval.votes.length > 0) {
      votesList = approval.votes.map(vote => (
        <tr key={`approval_${this.props.approval.get('id')}_vote_${vote.id}`}>
          <td>&nbsp;</td>
          <td>{vote.approver.name}</td>
          <td>{vote.message}</td>
          <td>
            <FormattedMessage id={`agent.tickets.approvals.response.vote_type.${vote.vote_type}`} />
            <small style={{ paddingLeft: '5px', fontSize: '9px', color: '#9e9e9e' }}>
              {vote.created_at}
            </small>
          </td>
          <td>
            <Icon name={vote.vote_type === 'approve' ? faCheck : faTimes} />
          </td>
        </tr>
      ));
    } else {
      votesList = (
        <tr>
          <td colSpan="5" style={{ textAlign: 'center' }}>
            <FormattedMessage id="agent.tickets.approvals.no_votes" />
          </td>
        </tr>
      );
    }

    const responsesStyle = {
      display: this.state.showResponses ? '' : 'none'
    };

    const approversList = approval.approvers.length <= 2
      ? approval.approvers.map(approver => approver.name).join(', ')
      : `${approval.approvers.slice(0, 1).map(approver => approver.name)} + ${approval.approvers.length - 1} more`;

    let iconName;
    if (approval.status === 'pending') {
      iconName = faClock;
    } else if (approval.status === 'approved') {
      iconName = faCheck;
    } else {
      iconName = faTimes;
    }

    const approvalStatusIcon = <Icon name={iconName} />;

    let approvalDate = '';
    if (approval.status === 'cancelled') {
      approvalDate = approval.cancelled_at;
    } else if (approval.status !== 'pending') {
      approvalDate = approval.completed_at;
    }

    return [
      <tr key={`approval_${approval.id}_data`}>
        <td>
          <Icon name={faCaretRight} style={{ marginRight: '5px' }} />
          <a onClick={this.toggleVotes}>{approval.id}</a>
        </td>
        <td>{approval.name}</td>
        <td>
          {approval.description}
          <small style={{ paddingLeft: '5px', fontSize: '9px', color: '#9e9e9e' }}>
            {approval.created_at}
          </small>
        </td>
        <td>{approversList}</td>
        <td>{approval.required_approvals}</td>
        <td>{approval.required_rejections}</td>
        <td>
          <FormattedMessage id={`agent.tickets.approvals.status_name.${approval.status}`} />
          <small style={{ paddingLeft: '5px', fontSize: '9px', color: '#9e9e9e' }}>
            {approvalDate}
          </small>
        </td>
        <td style={{ textAlign: 'center' }}>
          {controls}
        </td>
        <td>
          {approvalStatusIcon}
        </td>
      </tr>,
      <tr key={`approval_${approval.id}_responses`} style={responsesStyle}>
        <td colSpan="9">
          <table style={{ tableLayout: 'fixed', width: '100%' }}>
            <colgroup>
              <col style={{ width: '40px' }} />
              <col style={{ width: '30%' }} />
              <col style={{ width: '20%' }} />
              <col style={{ width: '100px' }} />
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
