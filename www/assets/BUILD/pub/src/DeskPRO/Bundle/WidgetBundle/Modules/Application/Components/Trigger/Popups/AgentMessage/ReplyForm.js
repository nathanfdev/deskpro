import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ReplyForm extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    primaryAgent:    PropTypes.object,
    onClick:         PropTypes.func
  };

  render() {
    const { backgroundColor, textColor, primaryAgent } = this.props;
    const displayName = primaryAgent.get('display_name') || 'Agent';
    const firstName = displayName.split(' ')[0];

    const placeholder = portalPhrases.get('portal.chat.type_message_to', { '{firstName}': firstName });

    return (
      <form onClick={this.props.onClick}>
        <input type="text" placeholder={placeholder} />
        <button className="send-btn" style={{ backgroundColor, color: textColor }}>
          <i className="fa fa-chevron-right" aria-hidden="true" />
        </button>
      </form>
    );
  }
}
