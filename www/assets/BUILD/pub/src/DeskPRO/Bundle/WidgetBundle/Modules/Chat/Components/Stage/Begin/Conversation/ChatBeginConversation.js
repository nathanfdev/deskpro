import PropTypes from 'prop-types';
import React from 'react';
import { Field } from '@deskpro/react-forms';
import { Input } from 'DeskPRO/Component/Semantic/ReactForm';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { CustomField } from 'DeskPRO/Component/CustomField/CustomField';
import { CustomFieldSingleChoice } from 'DeskPRO/Component/CustomField/CustomFieldSingleChoice';
import { hasErrors } from 'DeskPRO/Component/Form/FormErrors';
import { UserInfoForm } from './UserInfoForm';
import { ChatBeginContainer } from '../ChatBeginContainer';
import { CustomFieldTemplate } from './CustomFieldTemplate';
import BannedMessage from '../BannedMessage';

export class ChatBeginConversation extends React.Component {

  static propTypes = {
    banned:       PropTypes.bool,
    errors:       PropTypes.object,
    onSubmit:     PropTypes.func,
    customFields: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      fields:  [],
      current: null
    };
  }

  componentDidMount() {
    this.prepareFormFields(this.props);
  }

  componentWillReceiveProps(newProps) {
    const { customFields } = this.props;
    if (newProps.customFields !== customFields || newProps.errors || newProps.submit) {
      this.prepareFormFields(newProps);
    }
  }

  onSubmit = () => {
    const { errors, onSubmit } = this.props;
    const { fields, current } = this.state;

    if (!errors && fields[current + 1]) {
      this.setState({
        current: current + 1
      });
    } else {
      onSubmit();
    }
  };

  getChatDepartmentField(props) {
    return (
      <UserInfoForm
        title={portalPhrases.get('portal.chat.label-department')}
        isSubmit={props.submit}
        errors={props.errors}
        onSubmit={this.onSubmit}
        field="chat_department"
        primaryColor={props.primaryColor}
      >
        <CustomFieldSingleChoice
          name="chat_department"
          {...ChatBeginContainer.getWidgetConfig(props.chatDepartments)}
        />
      </UserInfoForm>
    );
  }

  prepareFormFields(props) {
    const { customFields, widgetLanguage, errors, submit, loggedIn, primaryColor } = props;
    const { chatRequiredName, chatRequiredEmail } = props;

    const hiddenFields = customFields.valueSeq().filter(this.isHiddenField).map(customField =>
      <CustomField config={customField} key={customField.get('id')}>
        <CustomFieldTemplate />
      </CustomField>
    );

    let current = 0;
    const fields = [];

    if (!loggedIn) {
      fields.push(
        <UserInfoForm
          title={portalPhrases.get('portal.chat.label-name')}
          isSubmit={submit}
          onSubmit={this.onSubmit}
          field="name"
          errors={errors}
          required={chatRequiredName}
          primaryColor={primaryColor}
        >
          {hiddenFields}
          <Field select="name">
            <Input type="text" placeholder={portalPhrases.get('portal.chat.details-placeholder')} />
          </Field>
        </UserInfoForm>
      );
      fields.push(
        <UserInfoForm
          title={portalPhrases.get('portal.chat.label-email')}
          isSubmit={submit}
          onSubmit={this.onSubmit}
          field="email"
          errors={errors}
          required={chatRequiredEmail}
          primaryColor={primaryColor}
        >
          {hiddenFields}
          <Field select="email">
            <Input type="text" placeholder="email@example.com" />
          </Field>
        </UserInfoForm>
      );

      if (hasErrors(errors, 'email')) {
        current = 1;
      }
    }

    if (props.allowDepartmentSelection) {
      const departmentField = this.getChatDepartmentField(props);
      fields.push(departmentField);

      if (hasErrors(errors, 'chat_department')) {
        current = fields.indexOf(departmentField);
      }
    }

    customFields.valueSeq().filter(this.isNotHiddenField).forEach((customField) => {
      const field = (
        <CustomField
          config={customField}
          language={widgetLanguage}
          formErrors={errors}
          widgetOptions={{
            context:       [parent.document, window.widgetFrame.document],
            contentWindow: window.widgetFrame,
            ownerDocument: window.widgetFrame.document
          }}
        >
          <CustomFieldTemplate isSubmit={submit} onSubmit={this.onSubmit} hiddenFields={hiddenFields} />
        </CustomField>
      );

      fields.push(field);
      if (!current && hasErrors(errors, CustomField.getFieldPropertyPath({ config: customField }))) {
        current = fields.indexOf(field);
      }
    });

    if (!errors && submit) {
      current = fields.length - 1;
    }

    setTimeout(() => this.setState({ fields, current }), 0);
  }

  isHiddenField = customField => customField.get('widget_type') === 'hidden';
  isNotHiddenField = customField => !this.isHiddenField(customField);

  render() {
    const { fields, current } = this.state;
    const { banned } = this.props;

    return (
      <div>
        {banned && <BannedMessage />}
        {fields[current]}
      </div>
    );
  }
}
