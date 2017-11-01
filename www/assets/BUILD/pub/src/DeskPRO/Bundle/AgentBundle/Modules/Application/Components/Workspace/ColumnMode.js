import PropTypes from 'prop-types';
import React from 'react';
import { ChangeModeButton } from './ChangeModeButton';
import { ColumnSlider } from './ColumnSlider';

export class ColumnMode extends React.Component {

  static propTypes = {
    currentMode:        PropTypes.string.isRequired,
    columnDimensions:   PropTypes.number.isRequired,
    onChangeMode:       PropTypes.func.isRequired,
    onChangeDimensions: PropTypes.func.isRequired
  };

  render() {
    const { currentMode, columnDimensions, onChangeMode, onChangeDimensions } = this.props;

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
            onChange={onChangeMode}
          >

            <span className="workspace-state-item state-sidebar"></span>
            <span className="workspace-state-item left-column active"></span>
            <span className="workspace-state-item right-column active"></span>
          </ChangeModeButton>

          <ChangeModeButton type={'focus'}
            title={'Focus Mode'}
            activeType={currentMode}
            onChange={onChangeMode}
          >

            <span className="workspace-state-item state-sidebar"></span>
            <span className="workspace-state-item full-width-column active"></span>
          </ChangeModeButton>
        </div>

        {currentMode === 'column' &&
          <ColumnSlider columnDimensions={columnDimensions} onChangeDimensions={onChangeDimensions} />}

    </div>);
  }
}
