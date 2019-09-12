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

  loadApprovalRequests() {
    this.props.dispatch(actions.loadApprovalRequests(this.props.ticketId))
      .then((res) => {
        const approvals = Immutable.fromJS(res.data);
        this.setState({
          approvals,
        });
      });
  }

  createApprovalRequest = data => this.props.dispatch(actions.createApprovalRequest(this.props.ticketId, data))
    .then(approvalRequest => {
      const approvals = this.state.approvals.push(Immutable.fromJS(approvalRequest));
      this.setState({
        approvals
      });
    });

  cancelApprovalRequest = requestId => this.props.dispatch(actions.cancelApprovalRequest(this.props.ticketId, requestId))
    .then(approvalRequest => {
      const approvals = this.state.approvals;
      const index = approvals.findIndex(obj => obj.id === approvalRequest.id);

      if (index > -1) {
        approvals.splice(index, 1, Immutable.fromJS(approvalRequest));
        this.setState({
          approvals
        });
      }
    });

  acceptApprovalRequest = (requestId, data) => this.props.dispatch(actions.acceptApprovalRequest(this.props.ticketId, requestId, data))
    .then(approvalRequest => {
      const approvals = this.state.approvals;
      const index = approvals.findIndex(obj => obj.id === approvalRequest.id);

      if (index > -1) {
        approvals.splice(index, 1, Immutable.fromJS(approvalRequest));
        this.setState({
          approvals
        });
      }
    });

  rejectApprovalRequest = (requestId, data) => this.props.dispatch(actions.rejectApprovalRequest(this.props.ticketId, requestId, data))
    .then(approvalRequest => {
      const approvals = this.state.approvals;
      const index = approvals.findIndex(obj => obj.id === approvalRequest.id);

      if (index > -1) {
        approvals.splice(index, 1, Immutable.fromJS(approvalRequest));
        this.setState({
          approvals
        });
      }
    });

  render() {
    const props = this.props;
    return (
      <Approval
        approvals={this.state.approvals}
        createApprovalRequest={this.createApprovalRequest}
        cancelApprovalRequest={this.cancelApprovalRequest}
        acceptApprovalRequest={this.acceptApprovalRequest}
        rejectApprovalRequest={this.rejectApprovalRequest}
        {...props}
      />
    );
  }
}
