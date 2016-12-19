import React from 'react';
import { AbstractCustomField } from './AbstractCustomField';
import { Field, Input } from 'react-forms';

export class CustomFieldHidden extends AbstractCustomField {

  render() {
    const { name, config } = this.props;

    return (
      <Field select={name}>
        <Input key={config.get('id')} type="hidden" />
      </Field>
    );
  }
}
