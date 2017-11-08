import PropTypes from 'prop-types';
import React from 'react';
import jQuery from 'jquery';
import 'jquery-ui/slider';

export class ColumnSlider extends React.Component {

  static propTypes = {
    columnDimensions:   PropTypes.number.isRequired,
    onChangeDimensions: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { columnDimensions, onChangeDimensions } = this.props;
    const sliderOptions = {
      value: columnDimensions,
      min:   0,
      max:   100,
      step:  5,
      slide: (event, ui) => {
        if (ui.value < 20 || ui.value > 80) {
          return false;
        }

        onChangeDimensions(ui.value);
        return null;
      }
    };

    jQuery('#workspace-column-slider').slider(sliderOptions);
  }

  reset = () => {
    const { onChangeDimensions } = this.props;
    onChangeDimensions(0);
  };

  render() {
    const { columnDimensions } = this.props;

    return (
      <div className="dpw-workspace-state dpw-workspace-slider-container">
        <div>
          <h2>Column Dimensions <a href="#" onClick={this.reset}>Reset</a></h2>
          <div className="dpw-workspace-slider">
            <div className="dpw-workspace-slider-count-container">
              <span className="dpw-workspace-slider-count">{columnDimensions}%</span>
            </div>

            <div className="dpw-workspace-slider-slide-container">
              <span className="dpw-workspace-slider-slide" id="workspace-column-slider">
                <span className="slider-blocked-left" />
                <span className="slider-blocked-right" />
                <span className="slider-button ui-slider-handle" />
              </span>
            </div>
          </div>
        </div>
      </div>
    );
  }

}
