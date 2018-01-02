import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';
import 'intl-tel-input';
import 'intl-tel-input/lib/libphonenumber/build/utils';

class PhoneInput extends React.Component {

  static propTypes = {
    className: PropTypes.string,
    value:     PropTypes.string,
    onChange:  PropTypes.func.isRequired
  };

  componentDidMount() {
    const $input = $(this.input);
    const { value, onChange } = this.props;

    $input.intlTelInput({
      autoPlaceholder: true,
      autoFormat:      true,
      allowExtensions: true,
      nationalMode:    true
    });

    $input.intlTelInput('utilsLoaded');
    $input.bind('change keyup', () => {
      onChange($input.intlTelInput('getNumber'));
    });
    $input.intlTelInput('setNumber', `${value}`);
  }

  componentWillReceiveProps(newProps) {
    if (newProps.value) {
      $(this.input).intlTelInput('setNumber', `${newProps.value}`);
    }
  }

  render() {
    const { className } = this.props;

    return (
      <input
        className={className}
        type="text"
        ref={(c) => { this.input = c; }}
      />
    );
  }
}

export default PhoneInput;
