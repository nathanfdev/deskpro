import React from 'react';

export class NavFrame extends React.Component {
  render() {

    let outer, inner = this.props.children;
    if (this.props.children.length) {
      this.props.children.forEach((child) => {
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
          <div className="sidebar-wrapper" id="sidebar-wrapper">
            <a className="collapse-button" href="#"><i className="fa fa-angle-right"></i></a>
            <span className="collapse-controls">
              <span className="disc"></span>
              <span className="disc"></span>
              <i className="fa fa-caret-right"></i>
              <span className="disc"></span>
              <span className="disc"></span>
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
          <a href="#" className="slider-control"></a>
        </div>
    );
  }
}