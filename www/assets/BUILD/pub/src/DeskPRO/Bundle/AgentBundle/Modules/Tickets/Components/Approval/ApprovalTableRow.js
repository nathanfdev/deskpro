import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import moment from 'moment';
import { Input, Icon, Button } from '@deskpro/react-components';
import { faTimes, faCheck, faClock } from '@fortawesome/free-solid-svg-icons';

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
      requestMessage: '',
      showResponses:  false,
      showApprovers:  false,
      saving:         false,
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

    const data = {
      message: this.state.requestMessage
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

    const data = {
      message: this.state.requestMessage
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

  toggleApprovers = () => {
    this.setState({
      showApprovers: !this.state.showApprovers
    });
  };

  changeRequestMessage = (requestMessage) => {
    this.setState({ requestMessage });
  };

  render() {
    const { me } = this.props;
    const { requestMessage } = this.state;

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

        if (
          !this.state.showResponses &&
          (
            (approval.approvers.findIndex(approver => meId === approver.id) > -1 && approval.approvers_pending.includes(meId))
            || this.props.approval.getIn(['selected_approvers', 'has_all_agents'])
          )
        ) {
          controls = (
            <div>
              <Button
                style={{ marginRight: '3px' }}
                size="small"
                loading={this.state.saving}
                onClick={() => this.cancelApprovalRequest(approval.id)}
              >
                Cancel
              </Button>
              <Button
                style={{ marginRight: '3px', color: 'green' }}
                size="small" loading={this.state.saving}
                onClick={() => this.acceptApprovalRequest(approval.id)}
              >
                Approve
              </Button>
              <Button
                style={{ color: 'red' }}
                size="small"
                loading={this.state.saving}
                onClick={() => this.rejectApprovalRequest(approval.id)}
              >
                Reject
              </Button>
            </div>
          );
        } else {
          controls = (
            <div>
              <Button
                size="small"
                loading={this.state.saving}
                onClick={() => this.cancelApprovalRequest(approval.id)}
              >
                Cancel
              </Button>
            </div>
          );
        }
      }
    }

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

    const result = [
      <tr key={`approval_${approval.id}_data`}>
        <td>
          <a onClick={this.toggleVotes} style={{ width: '100%', display: 'block' }}>
            {approval.id}
          </a>
        </td>
        <td>{approval.name}</td>
        <td>
          {approval.description}
          <small style={{ paddingLeft: '5px', fontSize: '9px', color: '#9e9e9e' }}>
            {approval.created_at}
          </small>
        </td>
        <td>
          {approval.approvers.length <= 2
            ? approval.approvers.map(approver => approver.name).join(', ')
            : <div>
              {approval.approvers.slice(0, 1).map(approver => approver.name)}&nbsp;
              <a onClick={this.toggleApprovers}>+ {approval.approvers.length - 1} more</a>
            </div>}
          {this.state.showApprovers &&
            <div>{approval.approvers.slice(1).map(approver => approver.name).join(', ')}</div>
          }
        </td>
        <td>{approval.required_approvals}</td>
        <td>{approval.required_rejections}</td>
        <td>
          {controls
            ? <div>{controls}</div>
            : <div className="approval-status">
              <FormattedMessage id={`agent.tickets.approvals.status_name.${approval.status}`} />
              <small style={{ paddingLeft: '5px', fontSize: '9px', color: '#9e9e9e' }}>
                {approvalDate}
              </small>
              {approvalStatusIcon}
            </div>
          }
        </td>
      </tr>
    ];

    if (this.state.showResponses) {
      const voteApproverIds = approval.votes.map(vote => vote.approver.id);

      if (approval.votes.length > 0 || approval.approvers.length > 0) {
        approval.votes.forEach(vote => result.push(
          <tr key={`approval_${this.props.approval.get('id')}_vote_${vote.id}`} className="approval-request-row">
            <td>&nbsp;</td>
            <td>{vote.approver.name}</td>
            <td>{vote.message}</td>
            <td />
            <td />
            <td />
            <td>
              <div className="approval-status">
                <FormattedMessage id={`agent.tickets.approvals.response.vote_type.${vote.vote_type}`} />
                <small style={{ paddingLeft: '5px', fontSize: '9px', color: '#9e9e9e' }}>
                  {vote.created_at}
                </small>
                <Icon name={vote.vote_type === 'approve' ? faCheck : faTimes} />
              </div>
            </td>
          </tr>
        ));
        approval.approvers.forEach((approver) => {
          if (voteApproverIds.indexOf(approver.id) === -1) {
            const hasButtons = approval.status === 'pending' && me.get('id') === approver.id;

            result.push(
              <tr key={`approval_${this.props.approval.get('id')}_vote_approver_${approver.id}`} className="approval-request-row">
                <td>&nbsp;</td>
                <td>{approver.name}</td>
                <td colSpan="4">
                  {hasButtons &&
                  <Input
                    type="text"
                    value={requestMessage}
                    onChange={this.changeRequestMessage}
                  />}
                </td>
                <td>
                  {approval.status === 'pending' &&
                  <div>
                    {hasButtons
                      ? <div>
                        <Button
                          style={{ marginRight: '3px', color: 'green' }}
                          size="small" loading={this.state.saving}
                          onClick={() => this.acceptApprovalRequest(approval.id)}
                        >
                          Approve
                        </Button>
                        <Button
                          style={{ color: 'red' }}
                          size="small"
                          loading={this.state.saving}
                          onClick={() => this.rejectApprovalRequest(approval.id)}
                        >
                          Reject
                        </Button>
                      </div>
                      : <div className="approval-status">
                        <FormattedMessage id="agent.tickets.approvals.status_name.pending" /> <Icon name={faClock} />
                      </div>
                    }
                  </div>}
                </td>
              </tr>
            );
          }
        });
      } else {
        result.push(
          <tr>
            <td colSpan="9">
              <FormattedMessage id="agent.tickets.approvals.no_votes" />
            </td>
          </tr>
        );
      }
    }

    return result;
  }
}

export default ApprovalTableRow;
