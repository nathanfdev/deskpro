import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import * as approvalActions from "../../Actions/approvalActions";
import {Approval} from "./Approval";

@connect(state => ({
  people: allPeopleSelector(state),
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
    this.loadApprovals();
  }

  componentWillMount() {
    window.document.addEventListener('dpApprovalUpdate', this.dpApprovalUpdate);
  }

  componentWillUnmount() {
    window.document.removeEventListener('dpApprovalUpdate', this.dpApprovalUpdate);
  }

  dpApprovalUpdate = (e) => {
    if (e.detail.ticketId === this.props.ticketId) {
      this.loadApprovals();
    }
  };

  loadApprovals() {
    this.props.dispatch(approvalActions.loadApprovals(this.props.ticketId))
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
