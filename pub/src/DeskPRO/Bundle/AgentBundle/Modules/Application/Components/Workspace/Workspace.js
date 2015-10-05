import React, { PropTypes } from 'react';

export default class Workspace extends React.Component {
  render() {
    return (<div style={{width: '240px', margin: '200px', background: '#f5f6f7'}}>

    <div className="dpw-workspace-type-container">

      <div className="dpw-workspace-type-header">
        <span className="title">Column Mode</span>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
      </div>

      <div className="dpw-workspace-state">

      <div className="workspace-state-a active">
        <div className="workspace-state-screen">
          <span className="active-workspace-mark"><i className="fa fa-check"></i></span>
          <span className="workspace-state-item state-sidebar"></span>
          <span className="workspace-state-item left-column active"></span>
          <span className="workspace-state-item right-column active"></span>
        </div>
        <span className="workspace-state-title">Column Mode</span>
      </div>

      <div className="workspace-state-b">
      <div className="workspace-state-screen">
        <span className="workspace-state-item state-sidebar"></span>
        <span className="workspace-state-item full-width-column active"></span>
      </div>
      <span className="workspace-state-title">Focus Mode</span>
    </div>
    </div>


    <div className="dpw-workspace-state dpw-workspace-slider-container">
      <div className="">
        <h2>Column Dimensions <a href="#">Reset</a></h2>
        <div className="dpw-workspace-slider">
          <div className="dpw-workspace-slider-count-container">
            <span className="dpw-workspace-slider-count">50%</span>
          </div>

          <div className="dpw-workspace-slider-slide-container">
                <span className="dpw-workspace-slider-slide">
                  <span className="slider-blocked-left"></span>
                  <span className="slider-blocked-right"></span>
                  <span className="slider-button"></span>
                </span>
          </div>
        </div>
      </div>

      </div>


      </div>
        <div className="dpw-workspace-type-container">

          <div className="dpw-workspace-type-header">
            <span className="title">Sidebar Mode</span>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
          </div>

          <div className="dpw-workspace-state">

            <div className="workspace-state-a">
              <div className="workspace-state-screen">
                <span className="workspace-state-item state-sidebar active"></span>
                <span className="workspace-state-item left-column"></span>
                <span className="workspace-state-item right-column"></span>
              </div>
              <span className="workspace-state-title">Static Mode</span>
            </div>

            <div className="workspace-state-b active">
              <div className="workspace-state-screen">
                <span className="active-workspace-mark"><i className="fa fa-check"></i></span>
                <span className="workspace-state-item state-sidebar state-sidebar-hover active"><i className="fa fa-asterisk"></i></span>
                <span className="workspace-state-item left-column"></span>
                <span className="workspace-state-item right-column"></span>
              </div>
              <span className="workspace-state-title">Hover Mode</span>
            </div>
          </div>

        </div>

        <div className="dpw-top-bar-dropdown-footer">
          <a href="#" className="dpw-top-bar-dropdown-button">Save Workspace</a>
          <a href="#" className="dpw-top-bar-dropdown-button blank">Reset All</a>
        </div>

      </div>);
  }
}
