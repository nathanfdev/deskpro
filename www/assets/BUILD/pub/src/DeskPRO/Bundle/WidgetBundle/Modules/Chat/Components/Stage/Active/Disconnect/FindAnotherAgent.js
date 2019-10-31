import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class FindAnotherAgent extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    onClick:   PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { agentName } = this.props;

    return (
      <div>
        <h1>{portalPhrases.get('portal.chat.agent_disconnected', { agentName })}</h1>
        <h2>{portalPhrases.get('portal.chat.find_another_agent')}</h2>
        <p>
          <a onClick={this.onClick}>
            {portalPhrases.get('portal.chat.find_agent_now')}
          </a>
        </p>
      </div>
    );
  }
}
