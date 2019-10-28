import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from '../../../../../AppBundle/Modules/RecordsStore';
import * as actions from '../../Actions/approvalRequestActions';
import { Approval } from './Approval';

@connect(state => ({
  agents:    agentsSelector(state),
  templates: allSelectorFactory('ApprovalTemplate')(state),
}))
export class ApprovalContainer extends React.Component {
  static propTypes = {
    ticketId: PropTypes.number,
    dispatch: PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.state = {
      approvals: Immutable.List()
    };
    this.loadApprovalRequests();
  }

  componentWillMount() {
    window.document.addEventListener('dpApprovalUpdate', this.dpApprovalUpdate);
  }

  componentWillUnmount() {
    window.document.removeEventListener('dpApprovalUpdate', this.dpApprovalUpdate);
  }

  getPeople = ids => this.props.dispatch(actions.loadApproversList(ids));
  getTemplateApprovers = approvalId => this.props.dispatch(actions.loadTemplateApprovers(approvalId, this.props.ticketId));

  getResponses = id => this.props.dispatch(actions.loadApprovalResponses(id));

  dpApprovalUpdate = (e) => {
    if (e.detail.ticketId === this.props.ticketId) {
      this.loadApprovalRequests();
    }
  };

  loadApprovalRequests() {
    this.props.dispatch(actions.loadApprovalRequests(this.props.ticketId))
      .then((response) => {
        const approvals = response.data;
        const promises = [];
        approvals.forEach((approval) => {
          approval.people = approval.approvers.map(approver => response.linked.person[approver]);
          const votesPromise = this.getResponses(approval.id);
          votesPromise.then(({ data }) => {
            approval.votes = data;
          });

          promises.push(votesPromise);
        });

        Promise.all(promises).then(() => {
          this.setState({
            approvals: Immutable.fromJS(approvals),
          });
        });
      });
  }

  createApprovalRequest = requestData => this.props.dispatch(actions.createApprovalRequest(this.props.ticketId, requestData))
    .then((approvalRequest) => {
      const allDone = [approvalRequest].reduce((promise, approval) =>
        promise.then(() =>
          Promise.all([
            this.getPeople(approval.approvers)
              .then(({ data }) => {
                approval.people = data;
              }),
            this.getResponses(approval.id)
              .then(({ data }) => {
                approval.votes = data;
              })
          ])
        )
      , Promise.resolve());

      allDone.then(() => {
        this.setState({
          approvals: this.state.approvals.push(Immutable.fromJS(approvalRequest))
        });
      });
    });

  cancelApprovalRequest = requestId => this.props.dispatch(actions.cancelApprovalRequest(requestId))
    .then(() => {
      const { approvals } = this.state;
      const index = approvals.toArray().findIndex(obj => obj.get('id') === requestId);

      if (index > -1) {
        this.setState({
          approvals: approvals.delete(index)
        });
      }
    });

  acceptApprovalRequest = (requestId, data = {}) => this.props.dispatch(actions.acceptApprovalRequest(requestId, data))
    .then(() => this.loadApprovalRequests());

  rejectApprovalRequest = (requestId, data = {}) => this.props.dispatch(actions.rejectApprovalRequest(requestId, data))
    .then(() => this.loadApprovalRequests());

  render() {
    return (
      <Approval
        approvals={this.state.approvals}
        getTemplateApprovers={this.getTemplateApprovers}
        createApprovalRequest={this.createApprovalRequest}
        cancelApprovalRequest={this.cancelApprovalRequest}
        acceptApprovalRequest={this.acceptApprovalRequest}
        rejectApprovalRequest={this.rejectApprovalRequest}
        {...this.props}
      />
    );
  }
}
