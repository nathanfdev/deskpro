import React, { PropTypes } from 'react';
import { FormItem } from './FormItem';
import { CustomFieldTemplate } from './CustomFieldTemplate';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { CustomField } from 'DeskPRO/Component/CustomField/CustomField';
import { Field, Input } from 'react-forms';
import { ChatBeginLoadingSpinner } from '../ChatBeginLoadingSpinner';

export class ChatBeginForm extends React.Component {

  static propTypes = {
    submit:             PropTypes.bool,
    errors:             PropTypes.object,
    onChange:           PropTypes.func,
    onSubmit:           PropTypes.func,
    customFields:       PropTypes.object,
    customFieldsLoaded: PropTypes.bool
  };

  render() {
    const { customFields, customFieldsLoaded, submit, errors, onSubmit } = this.props;

    if (!customFieldsLoaded) {
      return <ChatBeginLoadingSpinner />;
    }

    return (
      <div className="dpdesignportal-open-new-chat">
        <form className="dpdesignportal-form" onSubmit={onSubmit}>
          <FormItem label={portalPhrases.get('portal.chat.label-details')} field="name" errors={errors}>
            <Field select="name" placeholder={portalPhrases.get('portal.chat.details-placeholder')}>
              <Input type="text" />
            </Field>
          </FormItem>
          <FormItem label={portalPhrases.get('portal.chat.label-email')} field="email" errors={errors}>
            <Field select="email" placeholder="email@example.com">
              <Input type="email" />
            </Field>
          </FormItem>

          {customFields.valueSeq().map((customField, index) =>
            <CustomField
              key={index}
              config={customField}
              formErrors={errors}
              widgetOptions={{
                context:       [parent.document, window.widgetFrame.document],
                contentWindow: window.widgetFrame,
                ownerDocument: window.widgetFrame.document
              }}
            >
              <CustomFieldTemplate />
            </CustomField>
          )}

          <div className="button-label">
            {submit
              ? <div className="spinner"><i /></div>
              : <input
                type="submit"
                value={portalPhrases.get('portal.chat.start')}
                className="dpdesignportal-button dpdesignportal-button-wide"
              />
            }
          </div>
        </form>
      </div>
    );
  }
}
