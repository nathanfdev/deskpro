import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { closeWidget } from '../../../../Actions/dpWindowActions';
import { companyNameSelector, companyLogoSelector } from '../../../../Selectors/dpWindow';
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
    this.props.dispatch(closeWidget());
  };

  render() {
    return (
      <WidgetHeader onOpenMenu={this.onOpenMenu}
                    onClose={this.onClose} {...this.props} />
    );
  }
}
