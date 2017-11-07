import PropTypes from 'prop-types';
import React from 'react';
import jQuery from 'jquery';
import intlTelInput from 'intl-tel-input';

export class PhoneNumber extends React.Component {

  static propTypes = {
    number:    PropTypes.string,
    extension: PropTypes.string,
    onChange:  PropTypes.func.isRequired
  };

  componentDidMount() {
    const $input = jQuery(this.refs.phone);
    const { number, extension } = this.props;

    $input.intlTelInput({
      utilsScript:     DP_PHONE_UTIL_PATH,
      autoPlaceholder: true,
      autoFormat:      true,
      allowExtensions: true,
      nationalMode:    true
    });

    $input.intlTelInput('utilsLoaded');
    $input.bind('change keyup', () => {
      this.props.onChange($input.intlTelInput('getNumber'), $input.intlTelInput('getExtension'));
    });

    let displayNumber = number;
    if (extension) {
      displayNumber += ' ext. ' + extension;
    }

    $input.intlTelInput('setNumber', displayNumber || '');
  }

  render() {
    return (
      <input ref="phone"
        type="text"
        placeholder="Your phone number"
      />
    );
  }
}
