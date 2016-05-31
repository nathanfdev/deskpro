import React from 'react';
import { AbstractCustomField } from './AbstractCustomField';
import { Field, Input } from 'react-forms';

export class CustomFieldTextarea extends AbstractCustomField {

  render() {
    const { name } = this.props;

    return (
      <Field select={name}>
        <Input Component="textarea" />
      </Field>
    );
  }
}
