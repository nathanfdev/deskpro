import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, Input, createValue } from '@deskpro/react-forms';
import { css } from '../decorators';

const formErrors = {
  fields: {
    field3: {
      errors: [
        {
          code:    'required',
          message: 'This value should not be blank.'
        }
      ]
    }
  }
};

storiesOf('Semantic: @deskpro/react-forms', module)
  .addDecorator(story => css(story()))
  .add(
    'Simple form',
    () => (
      <Form formValue={createValue({ value: {}, errorList: formErrors })}>
        <Fieldset>
          <Field select="field1" label="Form field" />
          <Field select="field2" label="Field with placeholder">
            <Input placeholder="Field with placeholder" />
          </Field>
          <Field select="field3" label="Field with errors" />
        </Fieldset>
      </Form>
    )
  )
  .add(
    'Multiple field sets',
    () => (
      <Form formValue={createValue({})}>
        <Fieldset>
          <Field label="Form field" select="field1" />
        </Fieldset>
        <Fieldset>
          <Field label="Form field" select="field2" />
        </Fieldset>
      </Form>
    )
  )
;
