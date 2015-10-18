import React, { PropTypes } from 'react';
import { FieldWrapper } from './FieldWrapper';

export class Email extends React.Component {

  static propTypes = {
    errorMessage: PropTypes.string,
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { value, errorMessage, onChange } = this.props;

    return (
      <FieldWrapper iconClass="fa-user" label="Account name / Email" errorMessage={errorMessage}>
        <input type="text" placeholder="example@email.com" value={value} onChange={onChange} />
      </FieldWrapper>
    );
  }
}
