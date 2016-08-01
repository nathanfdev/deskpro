import React, { PropTypes } from 'react';

class Range extends React.Component {
  static propTypes = {
    value: PropTypes.int,
    onChange: PropTypes.func,
    elementId: PropTypes.string,
    min: PropTypes.int,
    max: PropTypes.int
  };
  static defaultProps = {
    onChange() {
    }
  };

  handleChange() {
    this.props.onChange(
      this.refs.rangeInput.value
    );
  }

  render() {
    const { elementId, value, min, max } = this.props;
    const props = {
      min,
      max
    };
    return  <input
      type="range"
      className="ui range"
      id={elementId}
      onChange={this.handleChange.bind(this)}
      ref="rangeInput"
      value={value}
      {...props}
    />
  }
}
export default Range;