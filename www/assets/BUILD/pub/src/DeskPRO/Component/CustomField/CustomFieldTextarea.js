import React from 'react';
import { Field } from '@deskpro/react-forms';
import { Textarea } from 'DeskPRO/Component/Semantic/ReactForm';
import { AbstractCustomField } from './AbstractCustomField';

export class CustomFieldTextarea extends AbstractCustomField {

  render() {
    const { name } = this.props;

    return (
      <Field select={name}>
        <Textarea />
      </Field>
    );
  }
}
