import PropTypes from 'prop-types';
import React from 'react';
import { ControlItem } from '../ControlItem';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class EndChatButton extends React.Component {

  static propTypes = {
    onOpenPopup: PropTypes.func
  };

  render() {
    return (
      <ControlItem onClick={this.props.onOpenPopup}>
        {portalPhrases.get('portal.chat.end_chat')} <i className="fa fa-power-off" />
      </ControlItem>
    );
  }
}
