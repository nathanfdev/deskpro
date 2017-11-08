import PropTypes from 'prop-types';
import React from 'react';
import { ControlItem } from '../ControlItem';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class MuteButton extends React.Component {

  static propTypes = {
    mute:    PropTypes.bool,
    onClick: PropTypes.func
  };

  render() {
    const { mute, onClick } = this.props;

    return (
      <ControlItem className="dpdesignportal-chat-header-control-mute" onClick={onClick}>
        <i className={classNames('fa', { 'fa-volume-up': !mute, 'fa-volume-off': mute })} />
        {portalPhrases.get('portal.chat.mute_button')}
      </ControlItem>
    );
  }
}
