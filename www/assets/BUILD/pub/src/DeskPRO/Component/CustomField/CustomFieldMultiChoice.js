import React from 'react';
import { Field } from '@deskpro/react-forms';
import { AbstractCustomField } from './AbstractCustomField';
import PortalSimpleSelectBoxWrapper from './PortalSimpleSelectBoxWrapper';

export class CustomFieldMultiChoice extends AbstractCustomField {

  render() {
    const { name, config, widgetOptions } = this.props;

    return (
      <Field select={name}>
        <PortalSimpleSelectBoxWrapper
          key={config.get('id')}
          multiple level={1}
          choices={config.get('choices')}
          widgetOptions={widgetOptions}
        />
      </Field>
    );
  }
}
