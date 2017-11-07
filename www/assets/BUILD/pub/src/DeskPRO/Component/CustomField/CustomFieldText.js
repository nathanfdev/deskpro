import React from 'react';
import { Field } from '@deskpro/react-forms';
import { Input } from 'DeskPRO/Component/Semantic/ReactForm';
import { AbstractCustomField } from './AbstractCustomField';

export class CustomFieldText extends AbstractCustomField {

  render() {
    const { name, config } = this.props;

    return (
      <Field select={name}>
        <Input key={config.get('id')} type="text" />
      </Field>
    );
  }
}
