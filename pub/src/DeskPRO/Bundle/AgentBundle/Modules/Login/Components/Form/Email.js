import React, { PropTypes } from 'react';
import { LoginFormField } from './LoginFormField';

export class Email extends React.Component {

  static propTypes = {
    hasError: PropTypes.bool,
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { hasError, value, onChange } = this.props;

    return (
      <LoginFormField iconClass="fa-user" label="Account name / Email" hasError={hasError}>
        <input type="text" placeholder="example@email.com" value={value} onChange={onChange} />
      </LoginFormField>
    );
  }
}
