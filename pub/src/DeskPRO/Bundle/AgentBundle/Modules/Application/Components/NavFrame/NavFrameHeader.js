import React from 'react';

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
          <h1>{this.props.title}</h1>
          <hr />
          <a href="#" className="slider-control"></a>
        </div>
    );
  }
}
