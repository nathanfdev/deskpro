import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import { loadAgents } from '../Actions/peopleActions';
import { allAgentsSelector, isAgentsLoadedSelector } from '../Selectors/people';

@connect(state => ({
  agents:       allAgentsSelector(state),
  agentsLoaded: isAgentsLoadedSelector(state)
}))
export class AgentsContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node
  };

  componentDidMount() {
    this.props.dispatch(loadAgents());
  }

  render() {
    const { children } = this.props;

    return React.cloneElement(children, { ...children.props, ...this.props });
  }
}

export class AgentChoiceListWrapper extends React.Component {

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
