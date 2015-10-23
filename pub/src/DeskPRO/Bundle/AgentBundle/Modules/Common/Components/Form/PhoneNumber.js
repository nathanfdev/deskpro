import React, { PropTypes } from 'react';
import jQuery from 'jquery';
import intlTelInput from 'intl-tel-input';

export class PhoneNumber extends React.Component {

  static propTypes = {
    number: PropTypes.string,
    extension: PropTypes.string,
    onChangeNumber: PropTypes.func.isRequired,
    onChangeExtension: PropTypes.func.isRequired
  };

  componentDidMount() {
    const $input = jQuery(this.refs.phone);

    $input.intlTelInput({
      utilsScript: `${DP_BUILD_PATH}/phonenumber_utils.js`,
      autoPlaceholder: true,
      autoFormat: true,
      allowExtensions: true,
      nationalMode: true
    });

    $input.intlTelInput('utilsLoaded');
    $input.bind('change keyup', () => {
      this.props.onChangeNumber($input.intlTelInput('getNumber'));
      this.props.onChangeExtension($input.intlTelInput('getExtension'));
    });


    $input.intlTelInput('setNumber', this.props.number || '');
    $input.intlTelInput('setExtension', this.props.extension || '');
  }

  render() {
    return (
      <input ref="phone"
             type="text"
             placeholder="Your phone number" />
    );
  }
}
