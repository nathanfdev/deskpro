import React from 'react';

export default class AppContainer extends React.Component {
  render() {
    const { dpWindow, thisAppId } = this.props;
    let classes = ['app-frame-container', 'app-active'];

    if (typeof dpWindow !== 'undefined' && dpWindow.get('collapseNav')) {
      classes.push('collapsed-nav');
    }

    // if(dpWindow.activeAppId == thisAppId) {
    //   classes.push('app-active');
    // } else {
    //   classes.push('app-inactive');
    // }
    return (
      <div className={classes.join(' ')}>
        {this.props.children}
      </div>
    );
  }
}
