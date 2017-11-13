import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import moment from 'moment';
import Immutable from 'immutable';
import { Container, Button, Checkbox } from '@deskpro/react-components';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
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
    if (confirm('Are you sure you want to delete this follow up?')) {
      const index = followUps.findIndex(f => f.get('id') === followUp.get('id'));
      this.props.dispatch(followUpActions.deleteFollowUp(this.props.ticketId, followUp.get('id')));
      this.setState({
        followUps: followUps.delete(index)
      });
    }
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
        <h4>{agentPhrases.get('agent.general.follow_ups')}</h4>
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
            {agentPhrases.get('agent.follow_up.new_follow_up')}
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
      saving:            false,
    };
  }

  convertTime = () => {
    const { dateToRun } = this.state;
    switch (dateToRun.type) {
      case 'selector':
      case 'preset':
        return moment().add(dateToRun.time.value, dateToRun.time.unit).format();
      case 'picker':
        return moment(dateToRun.time.date).format();
      default:
        throw Error('Unknown type');
    }
  };

  createFollowUp = () => {
    if (this.state.saving) {
      return false;
    }
    this.setState({
      saving: true
    });

    let errors = [];
    if (this.state.actions.length === 0) {
      errors.push(agentPhrases.get('agent.follow_up.you_must_add_one_action'));
    }
    if (!this.state.dateToRun.type) {
      errors.push(agentPhrases.get('agent.follow_up.you_must_select_time'));
    }
    errors = errors.concat(this.validateActions());
    this.setState({
      errors
    });
    if (errors.length === 0) {
      const dateToRun = this.convertTime();
      const { actions, cancelIfUserReply } = this.state;
      this.props.saveFollowUp({
        actions,
        date_to_run:          dateToRun,
        cancel_if_user_reply: cancelIfUserReply,
      }).catch((error) => {
        switch (error.data.status) {
          case 403:
            errors = ['You don\'t have the permission to create a follow up'];
            break;
          case 500:
            errors = ['There was an error with the error'];
            break;
          default:
            errors = ['There was an error please retry later or contact assistance'];
        }
        this.setState({
          saving: false,
          errors
        });
        console.log(error);
      });
    } else {
      this.setState({
        saving: false
      });
    }
    return true;
  };

  validateActions = () => {
    const { actions } = this.state;
    const errors = [];
    for (let i = 0; i < actions.length; i++) {
      for (let j = i + 1; j < actions.length; j++) {
        if (actions[i].type === actions[j].type && actions[i].type !== 'macro') {
          errors.push(`You can have only action of type "${actions[i].type}"`);
        }
      }
      const action = actions[i];
      switch (action.type) {
        case 'agent':
          if (typeof action.options.agent === 'undefined') {
            errors.push(agentPhrases.get('agent.follow_up.error_agent'));
          }
          break;
        case 'agent_team':
          if (typeof action.options.agent_team === 'undefined') {
            errors.push(agentPhrases.get('agent.follow_up.error_agent_team'));
          }
          break;
        case 'reply':
          if (typeof action.options.reply_text === 'undefined') {
            errors.push(agentPhrases.get('agent.follow_up.error_reply'));
          }
          break;
        case 'note':
          if (typeof action.options.reply_text === 'undefined') {
            errors.push(agentPhrases.get('agent.follow_up.error_note'));
          }
          break;
        case 'hold':
          if (typeof action.options.is_hold === 'undefined') {
            errors.push(agentPhrases.get('agent.follow_up.error_hold'));
          }
          break;
        case 'status':
          if (typeof action.options.status === 'undefined') {
            errors.push(agentPhrases.get('agent.follow_up.error_status'));
          }
          break;
        case 'macro':
          if (typeof action.options.macroId === 'undefined') {
            errors.push(agentPhrases.get('agent.follow_up.error_macro'));
          }
          break;
        default:
          throw Error('Unknown type');
      }
    }
    return errors;
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
    console.log(this.state.errors);
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
        <h4>{agentPhrases.get('agent.follow_up.add_follow_up')}</h4>
        <h5>{agentPhrases.get('agent.follow_up.follow_up_time')}</h5>
        <FollowUpTime
          value={this.state.dateToRun}
          onChange={this.updateTime}
        />
        <h5>{agentPhrases.get('agent.follow_up.follow_up_actions')}</h5>
        <ActionsBlock
          actions={this.state.actions}
          onChange={this.updateActions}
          agents={this.props.agents}
          agentTeams={this.props.agentTeams}
          macros={this.props.macros}
        />
        <h5>{agentPhrases.get('agent.general.criteria')}</h5>
        <Checkbox
          checked={this.state.cancelIfUserReply}
          onChange={this.updateCancelIfUserReply}
        >
          {agentPhrases.get('agent.follow_up.cancel_if_reply')}
        </Checkbox>
        <Button
          size="medium"
          onClick={this.createFollowUp}
          loading={this.state.saving}
        >
          {agentPhrases.get('agent.general.create')}
        </Button>
        {this.renderErrors()}
      </div>
    );
  }
}
