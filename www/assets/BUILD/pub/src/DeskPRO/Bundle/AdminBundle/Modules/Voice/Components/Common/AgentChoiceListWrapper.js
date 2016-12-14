import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';

class AgentChoiceListWrapper extends React.Component {

  static propTypes = {
    agents:   PropTypes.array,
    children: PropTypes.node
  };

  render() {
    const { children, agents = Immutable.fromJS([]) } = this.props;
    const choices = agents.map(agent => ({
      value: agent.get('id'),
      label: (
        <div className="multi-select-label">
          <PersonAvatar person={agent} size={16} />
          <span>{agent.get('name')}</span>
        </div>
      )
    })).toArray();

    return React.cloneElement(children, { ...children.props, ...this.props, choices });
  }
}

export default AgentChoiceListWrapper;
