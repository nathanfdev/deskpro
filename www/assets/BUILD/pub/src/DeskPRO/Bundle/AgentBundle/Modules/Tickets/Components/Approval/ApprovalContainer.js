import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { allSelectorFactory } from "../../../../../AppBundle/Modules/RecordsStore";
import * as actions from "../../Actions/approvalRequestActions";
import { Approval } from "./Approval";

@connect(state => ({
  people:    allSelectorFactory('Person')(state),
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

  render() {
    const props = this.props;
    return (
      <Approval
        approvals={this.state.approvals}
        {...props}
      />
    );
  }
}
