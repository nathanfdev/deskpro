import React, { PropTypes } from 'react';
import * as AppActions from '../../../Application/Actions/appActions';
import { connect } from 'react-redux';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

export class NavFrame extends React.Component {

  static propTypes = {
    children: PropTypes.any.isRequired
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

    return (
      <div>
        {outer}
        <section className="task-nav-frame dp-nav-frame">
          <div className="sidebar-wrapper">
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
