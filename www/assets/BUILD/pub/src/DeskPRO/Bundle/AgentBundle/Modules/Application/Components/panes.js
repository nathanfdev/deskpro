import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { workspaceSelector, workspaceDimsSelector } from '../Selectors/workspace';

function getWorkspaceClasses(workspace, classes = []) {
  if (!workspace.appNavPane) {
    classes.push('with-nav-off');
  } else {
    classes.push('with-nav-on');
  }
  if (!workspace.appListPane) {
    classes.push('with-list-off');
  } else {
    classes.push('with-list-on');
  }

  return classes;
}

@connect(state => ({
  workspace:     workspaceSelector(state),
  workspaceDims: workspaceDimsSelector(state)
}))
export class AppPane extends React.Component {
  static propTypes = {
    workspace:     PropTypes.object.isRequired,
    workspaceDims: PropTypes.object.isRequired
  };

  render() {
    const { workspace, workspaceDims } = this.props;
    const classes = getWorkspaceClasses(workspace, ['app-frame-container', 'app-active']);

    return (
      <div className="dp-panes-app" style={{width: workspaceDims.appPaneSize}}>
        <div className={classes.join(' ')}>
          {this.props.children}
        </div>
      </div>
    );
  }
}

@connect(state => ({
  workspace:     workspaceSelector(state),
  workspaceDims: workspaceDimsSelector(state)
}))
export class NavPane extends React.Component {
  static propTypes = {
    workspace:     PropTypes.object.isRequired,
    workspaceDims: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      isHover: false
    };

    this.timeout = null;
  }

  onMouseEnter = () => {
    if (this.timeout) {
      return;
    }

    this.timeout = window.setTimeout(() => {
      this.timeout = null;
      this.setState({ isHover: true });
    }, 350);
  };

  onMouseLeave = () => {
    if (this.timeout) {
      window.clearTimeout(this.timeout);
      this.timeout = null;
    }
    this.setState({ isHover: false });
  };

  onClick = () => {
    this.setState({ isHover: true });
  };

  render() {
    const { workspace, workspaceDims } = this.props;
    const classes = getWorkspaceClasses(workspace, ['dp-panes-nav']);

    if (this.state.isHover) {
      classes.push('with-nav-hover');
    }

    return (
      <div
        className={classes.join(' ')}
        onMouseEnter={this.onMouseEnter}
        onMouseLeave={this.onMouseLeave}
        onClick={this.onClick}
      >
        <div className="dp-collapsed-placeholder"></div>
        <div className="dp-panes-nav-body">{this.props.children}</div>
      </div>
    );
  }
}

@connect(state => ({
  workspace:     workspaceSelector(state),
  workspaceDims: workspaceDimsSelector(state)
}))
export class ListPane extends React.Component {
  static propTypes = {
    workspace:     PropTypes.object.isRequired,
    workspaceDims: PropTypes.object.isRequired
  };

  render() {
    const { workspace, workspaceDims } = this.props;
    const classes = getWorkspaceClasses(workspace, ['dp-panes-list']);

    return (
      <div className={classes.join(' ')} onMouseEnter={this.onMouseEnter} onMouseLeave={this.onMouseLeave}>
        {this.props.children}
      </div>
    );
  }
}

@connect(state => ({
  workspace:     workspaceSelector(state),
  workspaceDims: workspaceDimsSelector(state)
}))
export class TabBodyPane extends React.Component {
  static propTypes = {
    workspace:     PropTypes.object.isRequired,
    workspaceDims: PropTypes.object.isRequired
  };

  render() {
    const { workspace, workspaceDims } = this.props;
    const classes = getWorkspaceClasses(workspace, ['dp-panes-tabbody']);

    return (
      <div className={classes.join(' ')} style={{ left: workspaceDims.appPaneSize + 55 }}>
        {this.props.children}
      </div>
    );
  }
}
