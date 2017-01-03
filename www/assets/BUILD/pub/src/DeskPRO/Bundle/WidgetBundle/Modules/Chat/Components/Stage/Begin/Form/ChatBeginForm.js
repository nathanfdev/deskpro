import React, { PropTypes } from 'react';
import { Field, Input } from 'react-forms';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { CustomField } from 'DeskPRO/Component/CustomField/CustomField';
import { CustomFieldSingleChoice } from 'DeskPRO/Component/CustomField/CustomFieldSingleChoice';
import { FormItem } from './FormItem';
import { CustomFieldTemplate } from './CustomFieldTemplate';
import { WidgetBodyScrollAreaContainer } from '../../../../../Application/Components/Widget/Parts/Body/WidgetBodyScrollAreaContainer';
import { ChatBeginContainer } from '../ChatBeginContainer';

export class ChatBeginForm extends React.Component {

  static propTypes = {
    submit:                   PropTypes.bool,
    errors:                   PropTypes.object,
    onSubmit:                 PropTypes.func,
    customFields:             PropTypes.object,
    allowDepartmentSelection: PropTypes.bool,
    chatDepartments:          PropTypes.object,
    widgetLanguage:           PropTypes.number,
    loggedIn:                 PropTypes.bool
  };


  renderDepartmentSelect() {
    const { errors, chatDepartments } = this.props;

    return (
      <FormItem label={portalPhrases.get('portal.chat.label-department')} field="chat_department" errors={errors}>
        <CustomFieldSingleChoice
          name="chat_department"
          {...ChatBeginContainer.getWidgetConfig(chatDepartments)}
        />
      </FormItem>
    );
  }

  render() {
    const { customFields, allowDepartmentSelection, chatDepartments } = this.props;
    const { submit, errors, onSubmit, widgetLanguage, loggedIn } = this.props;

    return (
      <WidgetBodyScrollAreaContainer>
        <div className="dpdesignportal-open-new-chat">
          <form className="dpdesignportal-form" onSubmit={onSubmit}>
            {!loggedIn &&
              <FormItem label={portalPhrases.get('portal.chat.label-name')} field="name" errors={errors}>
                <Field select="name" placeholder={portalPhrases.get('portal.chat.details-placeholder')}>
                  <Input type="text" />
                </Field>
              </FormItem>}
            {!loggedIn &&
              <FormItem label={portalPhrases.get('portal.chat.label-email')} field="email" errors={errors}>
                <Field select="email" placeholder="email@example.com">
                  <Input type="email" />
                </Field>
              </FormItem>}
            {allowDepartmentSelection && chatDepartments.size > 1 && this.renderDepartmentSelect()}
            {customFields.valueSeq().map((customField, index) =>
              <CustomField
                key={index}
                config={customField}
                language={widgetLanguage}
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
                : <button className="dpdesignportal-button dpdesignportal-button-wide">
                  {portalPhrases.get('portal.chat.start')}
                </button>
              }
            </div>
          </form>
        </div>
      </WidgetBodyScrollAreaContainer>
    );
  }
}

export default ChatBeginForm;
