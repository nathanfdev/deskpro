import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { allSelectorFactory } from "../../../../../AppBundle/Modules/RecordsStore";
import * as actions from "../../Actions/approvalRequestActions";
import { Approval } from "./Approval";

@connect(state => ({
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

  dpApprovalUpdate = (e) => {
    if (e.detail.ticketId === this.props.ticketId) {
      this.loadApprovalRequests();
    }
  };

  getPeople = ids => this.props.dispatch(actions.loadApproversList(ids));

  getResponses = id => this.props.dispatch(actions.loadApprovalResponses(id));

  loadApprovalRequests() {
    this.props.dispatch(actions.loadApprovalRequests(this.props.ticketId))
      .then(({ data }) => {
        let approvals = data;//.filter(item => item.status !== 'cancelled');

        let allDone = approvals.reduce((promise, approval) => {
          return promise.then(() => {
            return Promise.all([
              this.getPeople(approval.approvers)
                .then(({ data }) => {
                  approval.people = data;
                }),
              this.getResponses(approval.id)
                .then(({ data }) => {
                  approval.votes = data;
                })
            ]);
          });
        }, Promise.resolve());

        allDone.then(e => {
          this.setState({
            approvals: Immutable.fromJS(approvals),
          });
        });

      });
  }

  createApprovalRequest = data => this.props.dispatch(actions.createApprovalRequest(this.props.ticketId, data))
    .then(approvalRequest => {

      let allDone = [approvalRequest].reduce((promise, approval) => {
        return promise.then(() => {
          return Promise.all([
            this.getPeople(approval.approvers)
              .then(({ data }) => {
                approval.people = data;
              }),
            this.getResponses(approval.id)
              .then(({ data }) => {
                approval.votes = data;
              })
          ]);
        });
      }, Promise.resolve());

      allDone.then(e => {
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
    .then(approvalResponse => {
      this.loadApprovalRequests();
    });

  rejectApprovalRequest = (requestId, data = {}) => this.props.dispatch(actions.rejectApprovalRequest(requestId, data))
    .then(approvalResponse => {
      this.loadApprovalRequests();
    });

  render() {
    return (
      <Approval
        approvals={this.state.approvals}
        getPeople={this.getPeople}
        createApprovalRequest={this.createApprovalRequest}
        cancelApprovalRequest={this.cancelApprovalRequest}
        acceptApprovalRequest={this.acceptApprovalRequest}
        rejectApprovalRequest={this.rejectApprovalRequest}
        {...this.props}
      />
    );
  }
}
