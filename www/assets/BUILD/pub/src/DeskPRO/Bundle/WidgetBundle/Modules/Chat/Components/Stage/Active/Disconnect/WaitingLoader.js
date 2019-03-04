import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class WaitingLoader extends React.Component {

  static propTypes = {
    agentName: PropTypes.string
  };

  render() {
    const { agentName } = this.props;

    return (
      <div>
        <h1>{portalPhrases.get('portal.chat.agent_disconnected', { agentName })}</h1>
        <h2>{portalPhrases.get('portal.chat.looking_for_another_agent')}</h2>
        <div className="search-dots">
          <div className="dot-1" />
          <div className="dot-2" />
          <div className="dot-3" />
          <div className="dot-4" />
          <div className="dot-5" />
        </div>
      </div>
    );
  }
}
