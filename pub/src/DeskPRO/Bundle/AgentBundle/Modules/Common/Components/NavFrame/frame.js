import React from 'react';
import * as AppActions from '../../../Application/Actions/AppActions';

export class NavFrame extends React.Component {
  render() {
    let outer, inner = this.props.children;
    if ((this.props.children instanceof Array) && this.props.children.length) {
      this.props.children.forEach((child) => {
        if (child.props.part === 'outer') {
          outer = child;
        } else if (child.props.part === 'inner') {
          inner = child;
        }
      });
    }

    const className = this.props.dpWindow && this.props.dpWindow.get('collapseNav')
                    ? 'sidebar-wrapper sidebar-collapsed'
                    : 'sidebar-wrapper';

    // Do nothing if we haven't passed in dispatch as a prop
    let expandNav = () => {};
    if (this.props.dispatch) {
      expandNav = () => this.props.dispatch(AppActions.expandNav());
    }

    return (
      <div>
        {outer}
        <section className="task-nav-frame dp-nav-frame">
          <div className={className} id="sidebar-wrapper">
            <a className="collapse-button" href="#" onClick={expandNav}>
              <i className="fa fa-angle-right"/>
            </a>
            <span className="collapse-controls" onClick={expandNav}>
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
  render() {
    const iconClass = 'fa ' + this.props.icon;
    const collapse = () => this.props.dispatch(AppActions.collapseNav());

    return (
      <div className="sidebar-title">
        <span className="sidebar-type-icon">
          <i className={iconClass}></i>
          <span className="help">
            <i className="fa fa-question"></i>
          </span>
        </span>

        <h1>{this.props.children}</h1>
        <hr />
        <a href="#" className="slider-control" onClick={collapse}/>
      </div>
    );
  }
}
