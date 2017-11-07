import PropTypes from 'prop-types';
import React from 'react';
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
    workspaceDims: PropTypes.object.isRequired,
    children:      PropTypes.node,
  };

  render() {
    const { workspace, workspaceDims } = this.props;
    const classes = getWorkspaceClasses(workspace, ['app-frame-container', 'app-active']);

    return (
      <div className="dp-panes-app" style={{ width: workspaceDims.appPaneSize }}>
        <div className={classes.join(' ')}>
          {this.props.children}
        </div>
      </div>
    );
  }
}

@connect(state => ({
  isHoverMode: !workspaceSelector(state).appNavPane
}))
export class NavPaneContainer extends React.Component {
  static propTypes = {
    isHoverMode: PropTypes.bool.isRequired,
    children:    PropTypes.node,
  };

  constructor(props) {
    super(props);

    // State to track mouse hover/leave if menu is in hover mode
    this.state = {
      isHovered: false
    };
    this.timeout = null;
  }

  onMouseEnter = () => {
    if (this.timeout) {
      return;
    }

    this.timeout = window.setTimeout(() => {
      this.timeout = null;
      this.setState({ isHovered: true });
    }, 350);
  };

  onMouseLeave = () => {
    if (this.timeout) {
      window.clearTimeout(this.timeout);
      this.timeout = null;
    }
    this.setState({ isHovered: false });
  };

  onClick = () => {
    this.setState({ isHovered: true });
  };

  render() {
    let jsx = (
      <NavPane isVisible={!this.props.isHoverMode || this.state.isHovered}>
        {this.props.children}
      </NavPane>
    );

    // Add mouse events if menu is in hover mode
    if (this.props.isHoverMode) {
      jsx = (
        <div
          onMouseEnter={this.onMouseEnter}
          onMouseLeave={this.onMouseLeave}
          onClick={this.onClick}
        >
          {jsx}
        </div>
      );
    }

    return jsx;
  }
}

export class NavPane extends React.Component {
  static propTypes = {
    isVisible: PropTypes.bool.isRequired,
    children:  PropTypes.node,
  };

  render() {
    let classNames = this.props.isVisible
      ? 'with-nav-on with-nav-hover'
      : 'with-nav-off';
    classNames += ' dp-panes-nav';

    return (
      <div className={classNames}>
        <div className="dp-collapsed-placeholder" />
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
    workspaceDims: PropTypes.object.isRequired,
    children:      PropTypes.node,
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
    workspaceDims: PropTypes.object.isRequired,
    children:      PropTypes.node,
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
