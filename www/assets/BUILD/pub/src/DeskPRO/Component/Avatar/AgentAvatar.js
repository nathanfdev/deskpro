import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Avatar from '@deskpro/react-components/lib/Components/Avatar';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

@connect(state => ({
  me:     meSelector(state),
  agents: agentsSelector(state)
}))
export default class AgentAvatar extends React.PureComponent {
  static propTypes = {
    me:     PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agent:  PropTypes.oneOfType([PropTypes.number, PropTypes.object]),
    size:   PropTypes.number
  };
  static defaultProps = {
    size: 18
  };

  render() {
    const { me, agents, size } = this.props;
    if (!this.props.agent) {
      return null;
    }

    let agent;
    if (!isNaN(this.props.agent)) {
      agent = agents.get(this.props.agent);
    } else if (this.props.agent.id) {
      agent = agents.get(this.props.agent.id);
    } else {
      agent = this.props.agent;
    }

    if (!agent) {
      return null;
    }

    let url = agent.getIn(['avatar', 'url_pattern'], false);
    if (!url) {
      url = agent.getIn(['avatar', 'default_url_pattern'], false);
    }
    let name = agent.get('display_name');
    if (agent.get('id') === me.get('id')) {
      name = agentPhrases.get('agent.general.me');
    }
    return <Avatar src={url.replace(/{{IMG_SIZE}}/, size)} title={name} />;
  }
}
