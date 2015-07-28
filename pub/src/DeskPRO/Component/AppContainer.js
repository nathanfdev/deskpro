import React from "react";

export default class AppContainer extends React.Component {
  render() {
    const { dp_window, thisAppId } = this.props;
    let classes = ["app-frame-container"];
    if(dp_window.activeAppId == thisAppId) {
      classes.push('app-active');
    } else {
      classes.push('app-inactive');
    }
    return (
      <div className={classes.join(" ")}>
        {this.props.children}
      </div>
    );
  }
}
