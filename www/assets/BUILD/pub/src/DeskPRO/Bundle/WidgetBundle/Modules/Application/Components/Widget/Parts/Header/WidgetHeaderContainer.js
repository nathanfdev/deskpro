import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { closeWidget, closeTriggerPopup } from '../../../../Actions/dpWindowActions';
import { unsetChatId } from '../../../../../Chat/Actions/chatActions';
import { companyNameSelector, companyLogoSelector } from '../../../../Selectors/bootstrap';
import { isEndedSelector } from '../../../../../Chat/Selectors/chat';
import { WidgetHeader } from './WidgetHeader';

@connect(state => ({
  companyName: companyNameSelector(state),
  companyLogo: companyLogoSelector(state),
  chatEnded:   isEndedSelector(state)
}))
export class WidgetHeaderContainer extends React.Component {

  static propTypes = {
    chatEnded: PropTypes.bool,
    dispatch:  PropTypes.func
  };

  onOpenMenu = () => {
    console.log('onOpenMenu');
  };

  onClose = () => {
    const { chatEnded, dispatch } = this.props;

    dispatch(closeWidget());

    this.props.dispatch(closeTriggerPopup());

    if (storageAvailable('sessionStorage')) {
      sessionStorage['dpWidget.dpWindow.popupShown'] = 'none';
      sessionStorage['dpWidget.dpWindow.minimized'] = true;
    }

    // If chat was ended then we can unset chat on close button
    if (chatEnded) {
      dispatch(unsetChatId());
    }
  };

  render() {
    return <WidgetHeader onOpenMenu={this.onOpenMenu} onClose={this.onClose} {...this.props} />;
  }
}
