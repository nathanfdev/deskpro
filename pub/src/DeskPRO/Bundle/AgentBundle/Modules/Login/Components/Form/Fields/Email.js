import React, { PropTypes } from 'react';
import { FieldWrapper } from './FieldWrapper';

export class Email extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <FieldWrapper iconClass="fa-user" label="Account name / Email" errorMessage="Looks like this isn't the correct password">
        <input type="text" placeholder="example@email.com" value={value} onChange={onChange} />
      </FieldWrapper>
    );
  }
}
