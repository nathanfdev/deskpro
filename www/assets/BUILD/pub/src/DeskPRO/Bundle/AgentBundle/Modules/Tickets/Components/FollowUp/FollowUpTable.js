import React from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';
import { Icon } from '@deskpro/react-components';

class FollowUpTable extends React.Component {
  static propTypes = {
    followUps:      PropTypes.object,
    agents:         PropTypes.object.isRequired,
    deleteFollowUp: PropTypes.func,
  };

  getAgent = (agentId) => {
    const agent = this.props.agents.find(a => a.get('id') === parseInt(agentId, 10));
    if (agent) {
      return agent.get('name');
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
        return `Agent: Assign ${this.getAgent(action.getIn(['options', 'agent']))}`;
      case 'status': {
        const statuses = [
          { awaiting_agent: 'agent.tickets.status_awaiting_agent' },
          { awaiting_user: 'agent.tickets.status_awaiting_user' },
          { resolved: 'agent.tickets.status_resolved' },
        ];
        const status = statuses[action.getIn(['options', 'status'])];
        return `Status: ${status}`;
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
            <th />
          </tr>
        </thead>
        <tbody>
          {this.props.followUps.map(followUp =>
            <tr key={followUp.get('id')}>
              <td title={moment(followUp.get('date_to_run')).format('YYYY-MM-DD H:mm:ss')}>
                {moment(followUp.get('date_to_run')).fromNow()}
              </td>
              <td>{this.getAgent(followUp.get('person'))}</td>
              <td>
                <ul>
                  {followUp.get('actions').map((action, i) => <li key={i}>{this.renderAction(action)}</li>)}
                </ul>
              </td>
              <td>
                {this.getCriteria(followUp)}
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
        </tbody>
      </table>
    );
  }
}
export default FollowUpTable;
