import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { ChangeModeButton } from './ChangeModeButton';

export class ColumnMode extends React.Component {

  static propTypes = {
    state: PropTypes.object.isRequired,
    onChangeMode: PropTypes.func.isRequired,
    onChangeDimensions: PropTypes.func.isRequired
  };

  render() {
    const { state, onChangeMode, onChangeDimensions } = this.props;
    const currentMode = state.columnMode;
    const dimensionsEnabled = false;

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
                            onChange={onChangeMode}>

            <span className="workspace-state-item state-sidebar"></span>
            <span className="workspace-state-item left-column active"></span>
            <span className="workspace-state-item right-column active"></span>
          </ChangeModeButton>

          <ChangeModeButton type={'focus'}
                            title={'Focus Mode'}
                            activeType={currentMode}
                            onChange={onChangeMode}>

            <span className="workspace-state-item state-sidebar"></span>
            <span className="workspace-state-item full-width-column active"></span>
          </ChangeModeButton>
        </div>

        {dimensionsEnabled ? (<div className="dpw-workspace-state dpw-workspace-slider-container">
          <div className="">
            <h2>Column Dimensions <a href="#" onClick={onChangeDimensions.bind(this, 0)}>Reset</a></h2>
            <div className="dpw-workspace-slider">
              <div className="dpw-workspace-slider-count-container">
                <span className="dpw-workspace-slider-count">{state.columnDimensions}%</span>
              </div>

              <div className="dpw-workspace-slider-slide-container">
                  <span className="dpw-workspace-slider-slide" ref="slider">
                    <span className="slider-blocked-left"></span>
                    <span className="slider-blocked-right"></span>
                    <span className="slider-button" style={{left: state.columnDimensions}}></span>
                  </span>
              </div>
            </div>
          </div>
        </div>) : null}

    </div>);
  }
}
