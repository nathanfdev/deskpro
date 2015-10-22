import React, { PropTypes } from 'react';
import jQuery from 'jquery';
import intlTelInput from 'intl-tel-input';

export class PhoneNumber extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    jQuery(this.refs.phone).intlTelInput();
  }

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  render() {
    return (
      <input ref="phone" type="text" placeholder="Your phone number" value={this.props.value} onChange={this.onChange} />
    );
  }
}
