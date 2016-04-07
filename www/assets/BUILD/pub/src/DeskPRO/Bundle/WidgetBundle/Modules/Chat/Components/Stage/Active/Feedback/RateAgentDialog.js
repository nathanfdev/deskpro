import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { agentNameSelector } from '../../../../Selectors/chat';

@connect(state => ({
  agentName: agentNameSelector(state)
}))
export class RateAgentDialog extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    onClickHelpful: PropTypes.func,
    onClickNotHelpful: PropTypes.func
  };

  onClickHelpful = event => {
    event.preventDefault();
    this.props.onClickHelpful();
  };

  onClickNotHelpful = event => {
    event.preventDefault();
    this.props.onClickNotHelpful();
  };

  render() {
    const { agentName } = this.props;
    // portal.chat.rate_agent_title
    return (
      <div className="dpdesignportal-agent-rating">
        <div></div>
        <h1><span>You just completed a chat with</span> {agentName}</h1>
        <p>{portalPhrases.get('portal.chat.rate_agent_desc')}</p>

        <div className="dpdesignportal-agent-rating-buttons">
          <a href="#" className="dpdesignportal-button" onClick={this.onClickHelpful}>
            <i className="fa fa-thumbs-up"></i> {portalPhrases.get('portal.chat.helpful')}
          </a>
          <a href="#" className="dpdesignportal-button negative" onClick={this.onClickNotHelpful}>
            <i className="fa fa-thumbs-down"></i> {portalPhrases.get('portal.chat.not_helpful')}
          </a>
        </div>
      </div>
    );
  }
}
