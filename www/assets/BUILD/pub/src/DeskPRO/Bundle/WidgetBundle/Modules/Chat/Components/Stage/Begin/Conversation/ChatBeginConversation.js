import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { Field, Input } from 'react-forms';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { CustomField } from 'DeskPRO/Component/CustomField/CustomField';
import { CustomFieldSingleChoice } from 'DeskPRO/Component/CustomField/CustomFieldSingleChoice';
import { hasErrors } from 'DeskPRO/Component/Form/FormErrors';
import { UserInfoForm } from './UserInfoForm';
import { ChatBeginLoadingSpinner } from '../ChatBeginLoadingSpinner';
import { CustomFieldTemplate } from './CustomFieldTemplate';

export class ChatBeginConversation extends React.Component {

  static propTypes = {
    errors:                PropTypes.object,
    onSubmit:              PropTypes.func,
    customFields:          PropTypes.object,
    customFieldsLoaded:    PropTypes.bool,
    chatDepartments:       PropTypes.object,
    chatDepartmentsLoaded: PropTypes.bool
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
    const { customFields, customFieldsLoaded, chatDepartments, chatDepartmentsLoaded } = this.props;

    if (newProps.customFields !== customFields
        || newProps.chatDepartments !== chatDepartments
        || newProps.chatDepartmentsLoaded !== chatDepartmentsLoaded
        || newProps.customFieldsLoaded !== customFieldsLoaded
        || newProps.errors
        || newProps.submit) {
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
      props.chatDepartments.map((dep) => {
        if (dep.get('parent') === department.get('id')) {
          choice.children = [];
          rec(dep, choice.children);
        }

        return dep;
      });

      choices.push(choice);
      processed[department.get('id')] = true;
    };

    props.chatDepartments.map((department) => {
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
      <UserInfoForm
        title={portalPhrases.get('portal.chat.label-department')}
        isSubmit={props.submit}
        errors={props.errors}
        onSubmit={this.onSubmit}
        field="chat_department"
      >
        <CustomFieldSingleChoice
          name="chat_department"
          config={Immutable.fromJS(config)}
          widgetOptions={widgetOptions}
        />
      </UserInfoForm>
    );
  }

  prepareFormFields(props) {
    const { customFields, errors, submit } = props;
    if (!props.customFieldsLoaded || (props.allowDepartmentSelection && !props.chatDepartmentsLoaded)) {
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

    if (props.allowDepartmentSelection) {
      fields.push(this.getChatDepartmentField(props));
    }

    if (hasErrors(errors, 'email')) {
      current = 1;
    }

    customFields.valueSeq().filter(this.isNotHiddenField).forEach((customField) => {
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
    const { fields, current } = this.state;
    const { customFieldsLoaded } = this.props;

    if (!customFieldsLoaded || !fields.length) {
      return <ChatBeginLoadingSpinner />;
    }

    return (
      <div>
        {fields[current]}
      </div>
    );
  }
}
