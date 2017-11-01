import PropTypes from 'prop-types';
import React from 'react';
import { ControlItem } from '../ControlItem';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ReopenChatButton extends React.Component {

  static propTypes = {
    isEnded:   PropTypes.bool,
    locked:    PropTypes.bool,
    canReopen: PropTypes.bool,
    onReopen:  PropTypes.func
  };

  render() {
    const { isEnded, locked, canReopen, onReopen } = this.props;

    return (
      <ControlItem onClick={onReopen} disabled={locked || (isEnded && !canReopen)}>
        {portalPhrases.get('portal.chat.reopen_chat_action')} <i className="fa fa-commenting-o" />
      </ControlItem>
    );
  }
}
