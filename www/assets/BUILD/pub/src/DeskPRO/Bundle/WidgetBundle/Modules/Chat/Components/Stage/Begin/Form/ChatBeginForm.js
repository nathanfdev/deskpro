import React, { PropTypes } from 'react';
import { Field, Input } from 'react-forms';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { CustomField } from 'DeskPRO/Component/CustomField/CustomField';
import { CustomFieldSingleChoice } from 'DeskPRO/Component/CustomField/CustomFieldSingleChoice';
import Immutable from 'immutable';
import { FormItem } from './FormItem';
import { CustomFieldTemplate } from './CustomFieldTemplate';
import { ChatBeginLoadingSpinner } from '../ChatBeginLoadingSpinner';
import { WidgetBodyScrollAreaContainer } from '../../../../../Application/Components/Widget/Parts/Body/WidgetBodyScrollAreaContainer';

export class ChatBeginForm extends React.Component {

  static propTypes = {
    submit:                   PropTypes.bool,
    errors:                   PropTypes.object,
    onSubmit:                 PropTypes.func,
    customFields:             PropTypes.object,
    customFieldsLoaded:       PropTypes.bool,
    allowDepartmentSelection: PropTypes.bool,
    chatDepartments:          PropTypes.object,
    chatDepartmentsLoaded:    PropTypes.bool
  };


  renderDepartmentSelect() {
    const config = {
      id:      0,
      choices: []
    };

    const processed = {};

    const rec = (department, choices) => {
      if (processed[department.get('id')]) {
        return;
      }
      const choice = {
        is_selectable: true,
        id:            department.get('id'),
        title:         department.get('user_title') || department.get('title')
      };
      this.props.chatDepartments.map((dep) => {
        if (dep.get('parent') === department.get('id')) {
          choice.children = [];
          rec(dep, choice.children);
        }

        return dep;
      });

      choices.push(choice);
      processed[department.get('id')] = true;
    };

    this.props.chatDepartments.map((department) => {
      if (!department.get('parent')) {
        rec(department, config.choices);
      }
      return department;
    });

    const widgetOptions = {
      context:       [parent.document, window.widgetFrame.document],
      contentWindow: window.widgetFrame,
      ownerDocument: window.widgetFrame.document
    };

    return (
      <FormItem label={portalPhrases.get('portal.chat.label-department')} field="chat_department" errors={this.props.errors}>
        <CustomFieldSingleChoice
          name="chat_department"
          config={Immutable.fromJS(config)}
          widgetOptions={widgetOptions}
        />
      </FormItem>
    );
  }

  render() {
    const { customFields,
            customFieldsLoaded,
            submit,
            errors,
            onSubmit,
            allowDepartmentSelection,
            chatDepartmentsLoaded
          } = this.props;

    if (!customFieldsLoaded) {
      return <ChatBeginLoadingSpinner />;
    }

    return (
      <WidgetBodyScrollAreaContainer>
        <div className="dpdesignportal-open-new-chat">
          <form className="dpdesignportal-form" onSubmit={onSubmit}>
            <FormItem label={portalPhrases.get('portal.chat.label-name')} field="name" errors={errors}>
              <Field select="name" placeholder={portalPhrases.get('portal.chat.details-placeholder')}>
                <Input type="text" />
              </Field>
            </FormItem>
            <FormItem label={portalPhrases.get('portal.chat.label-email')} field="email" errors={errors}>
              <Field select="email" placeholder="email@example.com">
                <Input type="email" />
              </Field>
            </FormItem>
            { allowDepartmentSelection && chatDepartmentsLoaded ? this.renderDepartmentSelect() : null }
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
