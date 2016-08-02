import React, { PropTypes } from 'react';

class Range extends React.Component {
  static propTypes = {
    value:     PropTypes.number,
    onChange:  PropTypes.func,
    elementId: PropTypes.string,
    min:       PropTypes.number,
    max:       PropTypes.number
  };
  static defaultProps = {
    onChange() {
    }
  };
  constructor() {
    super();
    this.handleChange = this.handleChange.bind(this);
  }

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
    return  (<input
      type="range"
      className="ui range"
      id={elementId}
      onChange={this.handleChange}
      ref="rangeInput"
      value={value}
      {...props}
    />);
  }
}
export default Range;
