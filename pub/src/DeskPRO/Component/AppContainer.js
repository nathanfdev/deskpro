import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class AppContainer extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    thisAppId: PropTypes.func.isRequired,
    children: PropTypes.object.isRequired
  };

  render() {
    const { dpWindow,/* thisAppId,*/ children } = this.props;
    const classes = ['app-frame-container', 'app-active'];

    if (dpWindow.get('collapseNav')) {
      classes.push('collapsed-nav');
    }
    if (dpWindow.get('columnMode') === 'focus') {
      classes.push('collapsed-list');
    }

    // if(dpWindow.activeAppId == thisAppId) {
    //   classes.push('app-active');
    // } else {
    //   classes.push('app-inactive');
    // }

    return (
      <div className={classes.join(' ')}>
        {children}
      </div>
    );
  }
}
