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
    onChange: () => {}
  };

  handleChange = () => {
    this.props.onChange(this.input.value);
  };

  render() {
    const { elementId, value, min, max } = this.props;
    const inputProps = { min, max };

    return (
      <input
        type="range"
        className="ui range"
        id={elementId}
        onChange={this.handleChange}
        ref={(c) => { this.input = c; }}
        value={value}
        {...inputProps}
      />
    );
  }
}

export default Range;
