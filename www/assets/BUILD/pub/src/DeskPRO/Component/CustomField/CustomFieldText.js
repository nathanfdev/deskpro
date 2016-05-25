import React from 'react';
import { AbstractCustomField } from './AbstractCustomField';
import { Field, Input } from 'react-forms';

export class CustomFieldText extends AbstractCustomField {

  render() {
    const { name } = this.props;

    return (
      <Field select={name}>
        <Input type="text" />
      </Field>
    );
  }
}
