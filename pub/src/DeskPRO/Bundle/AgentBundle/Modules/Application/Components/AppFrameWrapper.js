import React, { PropTypes } from 'react';

/**
 * Simple wrapper around app frames. This toggles the visible state in the
 * NavFrame and ListFrame based on the currently active app.
 */
export default class AppFrameWrapper extends React.Component {
  static propTypes = {
    activeAppId: PropTypes.string.isRequired,
    appId: PropTypes.string.isRequired
  }

  render() {
    const { appId, activeAppId } = this.props;
    const className = 'app-frame-container app-' + appId + ' ' + (appId === activeAppId ? 'app-active' : 'app-inactive');

    return (<div className={className}>
      {this.props.children}
    </div>);
  }
}
