import PropTypes from 'prop-types';
import React from 'react';
import { FieldWrapper } from './FieldWrapper';

export class Email extends React.Component {

  static propTypes = {
    errors:   PropTypes.object,
    value:    PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { value, errors, onChange } = this.props;

    return (
      <FieldWrapper iconClass="fa-user" label="Account name / Email" field="email" errors={errors}>
        <input type="text" placeholder="example@email.com" value={value} onChange={onChange} />
      </FieldWrapper>
    );
  }
}
