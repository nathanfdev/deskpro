import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentsSelector, isAgentsLoadedSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import AgentsVoiceToggle from './AgentsVoiceToggle';
import { loadAgents, editAgent } from '../../../Application/Actions/peopleActions';

@connect(state => ({
  agents:         agentsSelector(state),
  isAgentsLoaded: isAgentsLoadedSelector(state)
}))
class AgentsVoiceToggleContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    isAgentsLoaded: PropTypes.bool
  };

  componentDidMount() {
    this.props.dispatch(loadAgents());
  }

  onToggle = (agent) => {
    const { dispatch } = this.props;
    const data = {
      agent_data: {
        is_voice_enabled: !agent.getIn(['agent_data', 'is_voice_enabled'])
      }
    };

    return dispatch(editAgent(agent.get('id'), data));
  };

  render() {
    const { isAgentsLoaded } = this.props;

    if (!isAgentsLoaded) {
      return <LoadingPage />;
    }

    return (
      <AgentsVoiceToggle
        {...this.props}
        onToggle={this.onToggle}
      />
    );
  }
}

export default AgentsVoiceToggleContainer;
