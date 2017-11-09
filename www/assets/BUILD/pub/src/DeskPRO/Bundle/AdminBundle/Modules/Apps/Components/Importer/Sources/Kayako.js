import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import { Field, Input } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseSource from './BaseSource';

class Kayako extends BaseSource {

  getFormFields = () => (
    <div>
      <Fieldset className="field" select="dbinfo">
        <Field select="host" label="Host">
          <Input type="text" placeholder="localhost" />
        </Field>
        <Field select="port" label="Port">
          <Input type="text" placeholder="3306" />
        </Field>
        <Field select="user" label="Username">
          <Input type="text" placeholder="root" />
        </Field>
        <Field select="password" label="Password">
          <Input type="password" placeholder="" />
        </Field>
        <Field select="dbname" label="Database name">
          <Input type="text" placeholder="kayako" />
        </Field>
      </Fieldset>
    </div>
  );
}

export default Kayako;
