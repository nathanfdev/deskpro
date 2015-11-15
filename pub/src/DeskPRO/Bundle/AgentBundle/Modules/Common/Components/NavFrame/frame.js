import React, { PropTypes } from 'react';
import * as AppActions from '../../../Application/Actions/appActions';
import { connect } from 'react-redux';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class NavFrame extends React.Component {

  static propTypes = {
    children: PropTypes.any.isRequired,
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  onMouseEnter = () => {
    const { dpWindow, dispatch } = this.props;
    if (dpWindow.get('sidebarMode') === 'static') {
      return;
    }

    this.hoverTimeout = setTimeout(() => dispatch(AppActions.expandNav()), 250);
  };

  onMouseLeave = () => {
    const { dpWindow, dispatch } = this.props;
    if (dpWindow.get('sidebarMode') === 'static') {
      return;
    }

    clearTimeout(this.hoverTimeout);
    this.hoverTimeout = setTimeout(() => dispatch(AppActions.collapseNav()), 500);
  };

  render() {
    const { children, dpWindow } = this.props;
    let outer = '';
    let inner = children;

    if (children instanceof Array && children.length) {
      children.forEach(child => {
        if (child.props.part === 'outer') {
          outer = child;
        } else if (child.props.part === 'inner') {
          inner = child;
        }
      });
    }

    const className = ['sidebar-wrapper'];
    if (dpWindow.get('collapseNav')) {
      className.push('sidebar-collapsed');
    }

    return (
      <div>
        {outer}

        <section className="task-nav-frame dp-nav-frame"
                 onMouseEnter={this.onMouseEnter}
                 onMouseLeave={this.onMouseLeave}>

          <div className={className.join(' ')}>
            <aside className="sidebar has-tabs">
                {inner}
            </aside>
          </div>
        </section>
      </div>
    );
  }
}

export class NavFrameHeader extends React.Component {

  static propTypes = {
    children: PropTypes.any.isRequired,
    icon: PropTypes.string.isRequired
  };

  render() {
    const { children, icon } = this.props;
    const iconClass = 'icon ' + icon;

    return (
      <div>
        <div className="dpw-sidebar-main-title">
          <h1 className="dpw-sidebar-main-title-active-section-1">{children}</h1>

          <div className="dpw-sidebar-main-title-active-app-icon">
            <div className={iconClass}></div>
          </div>
        </div>
        <span className="dpw-sidebar-main-title-footer"></span>
      </div>
    );
  }
}

export class NavFrameBody extends React.Component {
  static propTypes = {
    children: PropTypes.any.isRequired
  };

  render() {
    return (
      <div className="dp-nav-frame-body">
        <Scrollable vertical>
          {this.props.children}
        </Scrollable>
      </div>
    );
  }
}
