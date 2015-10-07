import React, { PropTypes } from 'react';
import * as AppActions from '../../../Application/Actions/AppActions';

export class NavFrame extends React.Component {

  static propTypes = {
    children: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  render() {
    const { children, dpWindow } = this.props;
    let outer, inner = children;

    if (children instanceof Array && children.length) {
      children.forEach((child) => {
        if (child.props.part === 'outer') {
          outer = child;
        } else if (child.props.part === 'inner') {
          inner = child;
        }
      });
    }

    const className = ['sidebar-wrapper'];
    if (dpWindow.get('sidebarMode') === 'hover') {
      className.push('sidebar-collapsed');
    }

    return (
      <div>
        {outer}
        <section className="task-nav-frame dp-nav-frame">
          <div className={className.join(' ')} id="sidebar-wrapper">
            <a className="collapse-button" href="#">
              <i className="fa fa-angle-right"/>
            </a>
            <span className="collapse-controls">
              <span className="disc"/>
              <span className="disc"/>
              <i className="fa fa-caret-right"/>
              <span className="disc"/>
              <span className="disc"/>
            </span>
            <aside className="sidebar has-tabs" id="sidebar">
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
    children: PropTypes.object.isRequired,
    icon: PropTypes.string.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  render() {
    const { children, icon } = this.props;
    const iconClass = 'fa ' + icon;

    return (
      <div className="sidebar-title">
        <span className="sidebar-type-icon">
          <i className={iconClass}></i>
          <span className="help">
            <i className="fa fa-question"></i>
          </span>
        </span>

        <h1>{children}</h1>
        <hr />
        <a href="#" className="slider-control"/>
      </div>
    );
  }
}
