import React from 'react';
import { AbstractCustomField } from './AbstractCustomField';
import { Field, Input } from 'react-forms';

export class CustomFieldHidden extends AbstractCustomField {

  render() {
    const { name } = this.props;

    return (
      <Field select={name}>
        <Input type="hidden" />
      </Field>
    );
  }
}
