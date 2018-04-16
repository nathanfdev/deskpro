import React from 'react';
import PropTypes from 'prop-types';
import { Field } from '@deskpro/react-forms';
import $ from 'jquery';
import { AbstractCustomField } from './AbstractCustomField';
import PortalAttach from '../../Bundle/PortalBundle/React/Form/DropZone/PortalAttach';

export default class CustomFieldFile extends AbstractCustomField {

  render() {
    const { name, fieldType, config, widgetOptions } = this.props;

    return (
      <Field select={name}>
        <UploadWidget
          fieldId={config.get('id')}
          fieldType={fieldType}
          widgetOptions={widgetOptions}
        />
      </Field>
    );
  }
}

class UploadWidget extends React.Component {

  static propTypes = {
    onChange:      PropTypes.func,
    fieldId:       PropTypes.number,
    fieldType:     PropTypes.string,
    widgetOptions: PropTypes.object
  };

  render() {
    const { fieldId, fieldType, widgetOptions, onChange } = this.props;
    const $form = $('<form />');
    const $input = $('<input type="hidden" />');
    $input.on('blobs', (event, files) => onChange(files.map(file => file.info.authcode)));

    return (
      <div>
        <PortalAttach
          customField
          $input={$input}
          $form={$form}
          widgetOptions={widgetOptions}
          uploadUrl={`dpblob/custom_field.${fieldType}.${fieldId}`}
        />
      </div>
    );
  }
}
