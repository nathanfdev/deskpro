import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { closeWidget, closeTriggerPopup } from '../../../../Actions/dpWindowActions';
import { companyNameSelector, companyLogoSelector } from '../../../../Selectors/bootstrap';
import { WidgetHeader } from './WidgetHeader';

@connect(state => ({
  companyName: companyNameSelector(state),
  companyLogo: companyLogoSelector(state)
}))
export class WidgetHeaderContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onOpenMenu = () => {
    console.log('onOpenMenu');
  };

  onClose = () => {
    const { dispatch } = this.props;

    dispatch(closeWidget());

    this.props.dispatch(closeTriggerPopup());

    if (storageAvailable('sessionStorage')) {
      sessionStorage['dpWidget.dpWindow.popupShown'] = 'none';
      sessionStorage['dpWidget.dpWindow.minimized'] = true;
    }
  };

  render() {
    return <WidgetHeader onOpenMenu={this.onOpenMenu} onClose={this.onClose} {...this.props} />;
  }
}
