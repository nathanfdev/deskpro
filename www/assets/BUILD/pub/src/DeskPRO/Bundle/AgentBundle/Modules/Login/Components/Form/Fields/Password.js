import PropTypes from 'prop-types';
import React from 'react';
import { FieldWrapper } from './FieldWrapper';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';

export class Password extends React.Component {

  static propTypes = {
    errors:   PropTypes.object,
    value:    PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  isCapsLockWarningOpen() {
    const { value } = this.props;
    if (!value) {
      return false;
    }

    return value.length > 2 && value.toUpperCase() === value;
  }

  render() {
    const { value, errors, onChange } = this.props;

    return (
      <FieldWrapper iconClass="fa-lock" label="Password" field="password" errors={errors}>
        <Simple
          isOpen={this.isCapsLockWarningOpen()}
          positionTarget={this}
          positionAt="left top"
          positionMy="right center"
        >

          <div className="dpw-login-form-warning-container warning-container">
            <i className="fa fa-arrow-circle-o-up"></i> <span>Looks like caps lock is on?</span>
          </div>
        </Simple>

        <input type="password" placeholder="........." value={value} onChange={onChange} />
      </FieldWrapper>
    );
  }
}
