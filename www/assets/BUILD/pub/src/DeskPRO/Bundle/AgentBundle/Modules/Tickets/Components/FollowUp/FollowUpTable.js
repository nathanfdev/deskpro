import React from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';
import htmlToText from 'html-to-text';
import { Icon } from '@deskpro/react-components';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

class FollowUpTable extends React.Component {
  static propTypes = {
    followUps:      PropTypes.object,
    agents:         PropTypes.object.isRequired,
    agentTeams:     PropTypes.object.isRequired,
    macros:         PropTypes.object.isRequired,
    deleteFollowUp: PropTypes.func,
  };

  getAgent = (agentId) => {
    const agent = this.props.agents.find(a => a.get('id') === parseInt(agentId, 10));
    if (agent) {
      return agent.get('name');
    }
    return '';
  };

  getAgentTeam = (teamId) => {
    const team = this.props.agentTeams.find(t => t.get('id') === parseInt(teamId, 10));
    if (team) {
      return team.get('name');
    }
    return '';
  };

  getMacro = (macroId) => {
    const macro = this.props.macros.find(m => m.get('id') === parseInt(macroId, 10));
    if (macro) {
      return macro.get('title');
    }
    return '';
  };

  getCriteria = (followUp) => {
    if (followUp.get('cancel_if_user_reply')) {
      return 'Cancel if user reply';
    }
    return <span className="none">-</span>;
  };

  renderAction = (action) => {
    switch (action.get('type')) {
      case 'agent':
        return `Agent: Assign to ${this.getAgent(action.getIn(['options', 'agent']))}`;
      case 'team':
        return `Agent team: Assign to ${this.getAgentTeam(action.getIn(['options', 'agent_team']))}`;
      case 'macro':
        return `Macro: Run ${this.getMacro(action.getIn(['options', 'macroId']))}`;
      case 'status': {
        const statuses = {
          awaiting_agent: agentPhrases.get('agent.tickets.status_awaiting_agent'),
          awaiting_user:  agentPhrases.get('agent.tickets.status_awaiting_user'),
          resolved:       agentPhrases.get('agent.tickets.status_resolved')
        };
        const status = statuses[action.getIn(['options', 'status'])];
        return `Status: ${status}`;
      }
      case 'reply':
      case 'note': {
        const labels = {
          reply: 'Reply',
          note:  'Note',
        };
        return `${labels[action.get('type')]}: ${htmlToText.fromString(
          action.getIn(['options', 'reply_text']).substr(0, 100)
        )}`;
      }
      case 'hold':
        return `Hold: ${action.getIn(['options', 'hold']) ? 'Put ticket on hold' : 'Unhold ticket'}`;
      default:
        return null;
    }
  };

  render() {
    return (
      <table className="field-holders-table th-la">
        <thead>
          <tr>
            <th>When</th>
            <th>Agent</th>
            <th>Actions</th>
            <th>Criteria</th>
            <th>Status</th>
            <th />
          </tr>
        </thead>
        <tbody>
          {this.props.followUps
            .map(followUp =>
              <tr key={followUp.get('id')}>
                <td title={moment(followUp.get('date_to_run')).format('YYYY-MM-DD H:mm:ss')}>
                  {moment(followUp.get('date_to_run')).fromNow()}
                </td>
                <td>{this.getAgent(followUp.get('person'))}</td>
                <td>
                  <ul className="actions">
                    {followUp.get('actions').map((action, i) => <li key={i}>{this.renderAction(action)}</li>)}
                  </ul>
                </td>
                <td>
                  {this.getCriteria(followUp)}
                </td>
                <td>
                  {followUp.get('status')}
                </td>
                <td>
                  {followUp.get('status') === 'pending' ?
                    <Icon
                      className="delete-follow-up"
                      name="times"
                      onClick={() => this.props.deleteFollowUp(followUp)}
                    /> : null
              }
                </td>
              </tr>
        )}
          {this.props.followUps.size === 0 ?
            <tr>
              <td colSpan={6}>
              No Follow Ups
            </td>
            </tr>
        : null }
        </tbody>
      </table>
    );
  }
}
export default FollowUpTable;
