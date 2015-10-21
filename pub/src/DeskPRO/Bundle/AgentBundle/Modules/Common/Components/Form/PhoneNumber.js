import React, { PropTypes } from 'react';

export class PhoneNumber extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  render() {
    return (
      <input type="text" placeholder="Your phone number" value={this.props.value} onChange={this.onChange} />
    );
  }
}
