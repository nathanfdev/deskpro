import React, { PropTypes } from 'react';
import { ChangeModeButton } from './ChangeModeButton';

export class ColumnMode extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    onChangeMode: PropTypes.func.isRequired
  };

  setColumnDimensions(percent) {
    console.log(percent);
  }

  resetColumnDimensions() {
    this.setColumnDimensions(0);
  }

  changeMode = (mode) => {
    console.log('New SidebarMode', mode);
    console.log(mode);
  }

  render() {
    const { dpWindow, onChangeMode } = this.props;
    const currentMode = dpWindow.get('columnMode');

    return (
      <div className="dpw-workspace-type-container">

      <div className="dpw-workspace-type-header">
        <span className="title">Column Mode</span>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
      </div>

      <div className="dpw-workspace-state">
        <ChangeModeButton type={'column'}
                          title={'Column Mode'}
                          activeType={currentMode}
                          onChange={this.changeMode}>
          <span className="workspace-state-item state-sidebar"></span>
          <span className="workspace-state-item left-column active"></span>
          <span className="workspace-state-item right-column active"></span>
        </ChangeModeButton>

        <ChangeModeButton type={'focus'}
                          title={'Focus Mode'}
                          activeType={currentMode}
                          onChange={this.changeMode}>
          <span className="workspace-state-item state-sidebar"></span>
          <span className="workspace-state-item full-width-column active"></span>
        </ChangeModeButton>
      </div>

      <div className="dpw-workspace-state dpw-workspace-slider-container">
        <div className="">
          <h2>Column Dimensions <a href="#" onClick={this.resetColumnDimensions.bind(this)}>Reset</a></h2>
          <div className="dpw-workspace-slider">
            <div className="dpw-workspace-slider-count-container">
              <span className="dpw-workspace-slider-count">{dpWindow.get('columnDimensions')}%</span>
            </div>

            <div className="dpw-workspace-slider-slide-container">
                <span className="dpw-workspace-slider-slide">
                  <span className="slider-blocked-left"></span>
                  <span className="slider-blocked-right"></span>
                  <span className="slider-button" style={{left: dpWindow.get('columnDimensions')}}></span>
                </span>
            </div>
          </div>
        </div>
      </div>


    </div>);
  }
}
