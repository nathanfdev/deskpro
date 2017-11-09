import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import moment from 'moment';
import Immutable from 'immutable';
import { Container, Button, Checkbox } from '@deskpro/react-components';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import ActionsBlock from './ActionsBlock';
import FollowUpTime from './FollowUpTime';
import FollowUpTable from './FollowUpTable';
import * as followUpActions from '../../Actions/followUpActions';

@connect(state => ({
  agents:     agentsSelector(state),
  agentTeams: allSelectorFactory('AgentTeam')(state),
  macros:     allSelectorFactory('TicketMacros')(state)
}))
export class FollowUpContainer extends React.Component {
  static propTypes = {
    ticketId:    PropTypes.number,
    updateCount: PropTypes.func,
    dispatch:    PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      count:     0,
      followUps: Immutable.List()
    };
    this.props.dispatch(followUpActions.loadFollowUps(props.ticketId))
      .then((res) => {
        props.updateCount('', res.meta.pagination.total);
        this.setState({
          count:     res.meta.pagination.total,
          followUps: Immutable.fromJS(res.data),
        });
      });
  }

  saveFollowUp = (data) => {
    this.props.dispatch(followUpActions.createFollowUp(this.props.ticketId, data))
      .then((followUp) => {
        const followUps = this.state.followUps.push(Immutable.fromJS(followUp));
        this.setState({
          followUps
        });
        this.props.updateCount('+', 1);
      });
  };

  deleteFollowUp = (followUp) => {
    const { followUps } = this.state;
    const index = followUps.findIndex(f => f.get('id') === followUp.get('id'));
    this.setState({
      followUps: followUps.delete(index)
    });
  };

  render() {
    const props = this.props;
    return (
      <FollowUp
        followUps={this.state.followUps}
        deleteFollowUp={this.deleteFollowUp}
        saveFollowUp={this.saveFollowUp}
        {...props}
      />
    );
  }
}

export class FollowUp extends React.Component {
  static propTypes = {
    agents:         PropTypes.object.isRequired,
    agentTeams:     PropTypes.object.isRequired,
    followUps:      PropTypes.object.isRequired,
    macros:         PropTypes.object.isRequired,
    saveFollowUp:   PropTypes.func,
    deleteFollowUp: PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.state = {
      actions:           [{ type: 'reply' }],
      dateToRun:         {},
      cancelIfUserReply: false,
      errors:            [],
    };
  }

  updateActions = (actions) => {
    this.setState({
      actions
    });
  };

  updateCancelIfUserReply = (cancelIfUserReply) => {
    this.setState({
      cancelIfUserReply
    });
  };

  updateTime = (dateToRun) => {
    this.setState({
      dateToRun
    });
  };

  convertTime = () => {
    const { dateToRun } = this.state;
    switch (dateToRun.type) {
      case 'selector':
      case 'preset':
        return moment().add(dateToRun.time.value, dateToRun.time.unit).format();
      default:
        throw Error('Unknown type');
    }
  };

  createFollowUp = () => {
    const errors = [];
    if (this.state.actions.length === 0) {
      errors.push('You must add at least one action');
    }
    if (!this.state.dateToRun.time) {
      errors.push('You must select when the follow up will be performed');
    }
    const dateToRun = this.convertTime();
    this.setState({
      errors
    });
    if (errors.length === 0) {
      const { actions, cancelIfUserReply } = this.state;
      this.props.saveFollowUp({
        actions,
        date_to_run:          dateToRun,
        cancel_if_user_reply: cancelIfUserReply,
      });
    }
  };

  renderErrors = () => {
    if (this.state.errors.length === 0) {
      return null;
    }
    return (
      <div className="errors">
        <ul>
          {this.state.errors.map((error, index) => <li key={index}>{error}</li>)}
        </ul>
      </div>
    );
  };

  render() {
    return (
      <Container className="follow_up">
        <h4>Follow Ups</h4>
        <FollowUpTable
          followUps={this.props.followUps}
          agents={this.props.agents}
          deleteFollowUp={this.props.deleteFollowUp}
        />
        <h4>Add Follow Up</h4>
        <h5>Follow Up Time</h5>
        <FollowUpTime
          value={this.state.dateToRun}
          onChange={this.updateTime}
        />
        <h5>Follow Up Actions</h5>
        <ActionsBlock
          actions={this.state.actions}
          onChange={this.updateActions}
          agents={this.props.agents}
          agentTeams={this.props.agentTeams}
          macros={this.props.macros}
        />
        <h5>Criteria</h5>
        <Checkbox
          checked={this.state.cancelIfUserReply}
          onChange={this.updateCancelIfUserReply}
        >
          Cancel follow up if user replies
        </Checkbox>
        <Button
          size="medium"
          onClick={this.createFollowUp}
        >
          Create
        </Button>
        {this.renderErrors()}
      </Container>
    );
  }
}
