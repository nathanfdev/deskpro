import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { Container, Button, Checkbox } from '@deskpro/react-components';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { ActionsBlock } from './ActionsBlock';
import { FollowUpTime } from './FollowUpTime';
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
    this.props.dispatch(followUpActions.loadFollowUps({ ticketId: props.ticketId }))
      .then((res) => {
        console.log(res);
        props.updateCount(res.meta.pagination.total);
      });
  }

  render() {
    const props = this.props;
    return (
      <FollowUp
        {...props}
      />
    );
  }
}

export class FollowUp extends React.Component {
  static propTypes = {
    agents:     PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    macros:     PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      actions:       [{ type: 'reply' }],
      time:          {},
      cancelIfReply: false,
      errors:        [],
    };
  }

  updateActions = (actions) => {
    this.setState({
      actions
    });
  };

  updateCancelIfReply = (cancelIfReply) => {
    this.setState({
      cancelIfReply
    });
  };

  updateTime = (time) => {
    this.setState({
      time
    });
  };

  createFollowUp = () => {
    const errors = [];
    if (this.state.actions.length === 0) {
      errors.push('You must add at least one action');
    }
    if (!this.state.time.value) {
      errors.push('You must select when the follow up will be performed');
    }
    this.setState({
      errors
    });
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
        <h4>Add Follow Up</h4>
        <h5>Follow Up Time</h5>
        <FollowUpTime
          value={this.state.time}
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
          checked={this.state.cancelIfReply}
          onChange={this.updateCancelIfReply}
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
