import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';
import 'intl-tel-input';
import 'intl-tel-input/lib/libphonenumber/build/utils';
import Checkbox from './Checkbox';

class PhoneInput extends React.Component {

  static propTypes = {
    className:  PropTypes.string,
    value:      PropTypes.string,
    onChange:   PropTypes.func.isRequired,
    supportSip: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      isSip: false
    };
  }

  componentWillMount() {
    const { value } = this.props;
    if (/^sip:/.test(value)) {
      this.setState({ isSip: true });
    }
  }

  componentDidMount() {
    this.initInput();
  }

  setNumber(number) {
    const $input = $(this.input);
    const { onChange } = this.props;
    const { isSip } = this.state;

    if (isSip) {
      $input.val(`${number}`.replace(/^sip:/, ''));
      onChange(`sip:${$input.val()}`);
    } else {
      $input.intlTelInput('setNumber', `${number}`);
      onChange($input.intlTelInput('getNumber') || `${number}`);
    }
  }

  initInput() {
    const { isSip } = this.state;
    const $input = $(this.input);
    const { value, onChange } = this.props;

    if (isSip) {
      $input.bind('change keyup', () => {
        onChange(`sip:${$input.val()}`);
      });
      $input.val(`${value}`.replace(/^sip:/, ''));
    } else {
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
  }

  toggleSipMode = (isSip) => {
    this.setNumber('');
    this.setState({ isSip }, () => {
      this.initInput();

      const $input = $(this.input);
      $input.focus();
    });
  };

  render() {
    const { className, supportSip } = this.props;
    const { isSip } = this.state;

    return (
      <div>
        {isSip &&
        <div className="voice-sip-number">
          <span className="sip-label">sip:</span>
          <input
            className={className}
            type="text"
            ref={(c) => { this.input = c; }}
          />
        </div>
        }
        {!isSip &&
        <div className="voice-phone-number">
          <input
            className={className}
            type="text"
            ref={(c) => { this.input = c; }}
          />
        </div>}
        {supportSip &&
        <Checkbox
          value={isSip}
          onChange={this.toggleSipMode}
          label="SIP address"
          className="voice-sip-number-mode"
        />}
      </div>
    );
  }
}

export default PhoneInput;
