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
      followUps: Immutable.List()
    };
    this.loadFollowUps();
  }

  componentWillMount = () => {
    window.document.addEventListener('dpFollowUpUpdate', (e) => {
      if (e.detail.ticketId === this.props.ticketId) {
        this.loadFollowUps();
      }
    });
  };

  loadFollowUps = () => {
    this.props.dispatch(followUpActions.loadFollowUps(this.props.ticketId))
      .then((res) => {
        const followUps = Immutable.fromJS(res.data);
        this.props.updateCount('', followUps.count(f => f.get('status') === 'pending'));
        this.setState({
          followUps,
        });
      });
  };

  saveFollowUp = data => this.props.dispatch(followUpActions.createFollowUp(this.props.ticketId, data))
      .then((followUp) => {
        const followUps = this.state.followUps.push(Immutable.fromJS(followUp));
        this.setState({
          followUps
        });
        this.props.updateCount('+', 1);
      });

  deleteFollowUp = (followUp) => {
    const { followUps } = this.state;
    const index = followUps.findIndex(f => f.get('id') === followUp.get('id'));
    this.props.dispatch(followUpActions.deleteFollowUp(this.props.ticketId, followUp.get('id')));
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
      displayForm: false
    };
  }

  displayForm = () => {
    this.setState({
      displayForm: true
    });
  };

  saveFollowUp = data => this.props.saveFollowUp(data)
      .then(() => {
        this.setState({
          displayForm: false
        });
      });

  render() {
    return (
      <Container className="follow_up">
        <h4>Follow Ups</h4>
        <FollowUpTable
          followUps={this.props.followUps}
          agents={this.props.agents}
          agentTeams={this.props.agentTeams}
          macros={this.props.macros}
          deleteFollowUp={this.props.deleteFollowUp}
        />
        {this.state.displayForm ?
          <FollowUpForm
            agents={this.props.agents}
            agentTeams={this.props.agentTeams}
            macros={this.props.macros}
            saveFollowUp={this.saveFollowUp}
          />
          :
          <Button
            size="m"
            onClick={this.displayForm}
          >
            New Follow Up
          </Button>
        }
      </Container>
    );
  }
}

class FollowUpForm extends React.Component {
  static propTypes = {
    agents:       PropTypes.object.isRequired,
    agentTeams:   PropTypes.object.isRequired,
    macros:       PropTypes.object.isRequired,
    saveFollowUp: PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.state = {
      actions:           [{ type: 'reply', options: {} }],
      dateToRun:         {},
      cancelIfUserReply: false,
      errors:            [],
    };
  }

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
      <div>
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
      </div>
    );
  }
}
