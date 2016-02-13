import React, { PropTypes } from 'react';
import jQuery from 'jquery';
import 'jquery-ui/slider';

export class ColumnSlider extends React.Component {

  static propTypes = {
    columnDimensions: PropTypes.number.isRequired,
    onChangeDimensions: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { columnDimensions, onChangeDimensions } = this.props;

    jQuery('#workspace-column-slider').slider({
      value: columnDimensions,
      min: 0,
      max: 100,
      step: 5,
      slide: (event, ui) => {
        if (ui.value < 20 || ui.value > 80) {
          return false;
        }

        onChangeDimensions(ui.value);
      }
    });
  }

  render() {
    const { columnDimensions, onChangeDimensions } = this.props;

    return (
      <div className="dpw-workspace-state dpw-workspace-slider-container">
        <div>
          <h2>Column Dimensions <a href="#" onClick={onChangeDimensions.bind(this, 0)}>Reset</a></h2>
          <div className="dpw-workspace-slider">
            <div className="dpw-workspace-slider-count-container">
              <span className="dpw-workspace-slider-count">{columnDimensions}%</span>
            </div>

            <div className="dpw-workspace-slider-slide-container">
              <span className="dpw-workspace-slider-slide" id="workspace-column-slider">
                <span className="slider-blocked-left"></span>
                <span className="slider-blocked-right"></span>
                <span className="slider-button ui-slider-handle"></span>
              </span>
            </div>
          </div>
        </div>
      </div>
    );
  }

}