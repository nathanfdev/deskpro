import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class RateAgentDialog extends React.Component {

  static propTypes = {
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
    // portal.chat.rate_agent_title
    return (
      <div className="dpdesignportal-agent-rating">
        <div></div>
        <h1><span>You just completed a chat with</span> Noelle Gray</h1>
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
