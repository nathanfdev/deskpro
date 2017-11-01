import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';

class Range extends React.Component {

  static propTypes = {
    value:     PropTypes.number,
    onChange:  PropTypes.func,
    onBlur:    PropTypes.func,
    elementId: PropTypes.string,
    min:       PropTypes.number,
    max:       PropTypes.number,
    disabled:  PropTypes.bool
  };

  static defaultProps = {
    onChange: () => {},
    onBlur:   () => {}
  };

  componentDidMount() {
    $(this.input).on('mouseup', this.props.onBlur);
  }

  componentWillUnmount() {
    $(this.input).off('mouseup', this.props.onBlur);
  }

  handleChange = () => {
    this.props.onChange(this.input.value);
  };

  render() {
    const { elementId, value, min, max, disabled } = this.props;
    const inputProps = { min, max };

    return (
      <input
        type="range"
        className="ui range"
        id={elementId}
        onChange={this.handleChange}
        ref={(c) => { this.input = c; }}
        value={value}
        disabled={disabled}
        {...inputProps}
      />
    );
  }
}

export default Range;
