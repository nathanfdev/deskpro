import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';

@connect(state => ({
  agents: collectionSelectorFactory('Person', 'agents')(state)
}))
export class AgentList extends SeparateComponent {

  static propTypes = {
    agents: PropTypes.object.isRequired
  };

  static getType() {
    return 'AgentList';
  }

  render() {
    return (<ul className="nav-list nav-list-small">
      {this.props.agents.map(agent => <li>{agent.get('name')}</li>)}
    </ul>);
  }

}
