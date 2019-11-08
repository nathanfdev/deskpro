import PropTypes from 'prop-types';
import React from 'react';
import { defineMessages, injectIntl, intlShape } from 'react-intl';
import { connect } from 'react-redux';
import { Avatar } from '@deskpro/react-components';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

const messages = defineMessages({
  me: {
    id:             'agent.general.me',
    defaultMessage: 'Me'
  }
});

@injectIntl
@connect(state => ({
  me:     meSelector(state),
  agents: agentsSelector(state)
}))
export default class AgentAvatar extends React.PureComponent {
  static propTypes = {
    intl:      intlShape.isRequired,
    me:        PropTypes.object.isRequired,
    agents:    PropTypes.object.isRequired,
    agent:     PropTypes.oneOfType([PropTypes.number, PropTypes.object]),
    size:      PropTypes.number,
    border:    PropTypes.string,
    // Avatar component has predefined sizes.
    // forceSize prop pass size as width/height in style
    forceSize: PropTypes.bool
  };
  static defaultProps = {
    size: 18
  };

  getStyle() {
    const { border, forceSize, size } = this.props;
    const style = {};

    if (border) {
      style.border = border;
    }

    if (forceSize) {
      style.width = `${size}px`;
      style.height = `${size}px`;
    }

    return style;
  }

  getSizeText = (size) => {
    if (size <= 12) {
      return 'small';
    } else if (size <= 22) {
      return 'medium';
    } else if (size <= 26) {
      return 'large';
    }

    return 'xlarge';
  }

  render() {
    const { me, agents, size } = this.props;
    const { formatMessage } = this.props.intl;
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
      name = formatMessage(messages.me);
    }
    return (
      <Avatar
        src={url.replace(/{{IMG_SIZE}}/, size)}
        title={name}
        size={this.getSizeText(size)}
        style={this.getStyle()}
      />
    );
  }
}
