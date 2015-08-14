import React from 'react';

export class NavFrameTitle extends React.Component {
  render() {
    return (
        <div className="sidebar-title">
              <span className="sidebar-type-icon">
                <i className="fa fa-comments-o"></i>
                <span className="help">
                  <i className="fa fa-question"></i>
                </span>
              </span>
          <h1>Chat</h1>
          <hr />
          <a href="#" className="slider-control"></a>
        </div>
    );
  }
}
