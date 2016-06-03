import React, { PropTypes } from 'react';
import { UserInfoForm } from './UserInfoForm';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { Field, Input } from 'react-forms';
import { ChatBeginLoadingSpinner } from '../ChatBeginLoadingSpinner';
import { CustomField } from 'DeskPRO/Component/CustomField/CustomField';
import { CustomFieldTemplate } from './CustomFieldTemplate';
import { hasErrors } from 'DeskPRO/Component/Form/FormErrors';

export class ChatBeginConversation extends React.Component {

  static propTypes = {
    submit:             PropTypes.bool,
    errors:             PropTypes.object,
    onSubmit:           PropTypes.func,
    customFields:       PropTypes.object,
    customFieldsLoaded: PropTypes.bool
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
    if (newProps.customFields !== this.props.customFields || newProps.errors || newProps.submit) {
      this.prepareFormFields(newProps);
    }
  }

  onSubmit = () => {
    const { errors } = this.props;

    if (!errors && this.state.fields[this.state.current + 1]) {
      this.setState({
        current: this.state.current + 1
      });
    } else {
      this.props.onSubmit();
    }
  };

  prepareFormFields(props) {
    const { customFields, errors, submit } = props;
    if (!props.customFieldsLoaded) {
      return;
    }

    const hiddenFields = customFields.valueSeq().filter(this.isHiddenField).map(customField =>
      <CustomField config={customField} key={customField.get('id')}>
        <CustomFieldTemplate />
      </CustomField>
    );

    let current = 0;
    const fields = [];
    fields.push(
      <UserInfoForm
        title={portalPhrases.get('portal.chat.label-name')}
        isSubmit={submit}
        onSubmit={this.onSubmit}
        field="name"
        errors={errors}
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

    customFields.valueSeq().filter(this.isNotHiddenField).forEach(customField => {
      const field = (
        <CustomField
          config={customField}
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
    if (!this.props.customFieldsLoaded || !this.state.fields.length) {
      return <ChatBeginLoadingSpinner />;
    }

    return (
      <div>
        {this.state.fields[this.state.current]}
      </div>
    );
  }
}
