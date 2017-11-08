import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Fieldset, createValue } from '@deskpro/react-forms';
import $ from 'jquery';
import Immutable from 'immutable';
import { loadAll, loadWithParams, isLoadedCollectionSelectorFactory, collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import PortalFormWidget from 'DeskPRO/Bundle/PortalBundle/PageWidget/PortalFormWidget';
import { createChat } from '../../../Actions/chatActions';
import {
  liveDemoSelector,
  widgetLanguageSelector,
  chatFormDefaultValuesSelector,
  chatSelectDepartmentTypeSelector,
  chatDefaultDepartmentSelector,
  chatRequiredNameSelector,
  chatRequiredEmailSelector,
  primaryColorSelector
} from '../../../../Application/Selectors/dpWindow';
import { widgetSessionIsLoginSelector } from '../../../../Application/Selectors/bootstrap';
import { customChatFieldsOrderedSelector } from '../../../../Application/Selectors/customFields';
import { history } from '../../../../../Services/history';
import { ChatBeginLoadingSpinner } from './ChatBeginLoadingSpinner';
import { ChatBeginSimple } from './ChatBeginSimple';

@connect(state => ({
  liveDemo:                 liveDemoSelector(state),
  customFieldsLoaded:       isLoadedCollectionSelectorFactory('CustomDefChat', 'all')(state),
  customFields:             customChatFieldsOrderedSelector(state),
  chatDepartments:          collectionSelectorFactory('ChatDepartment', 'online')(state),
  chatDepartmentsLoaded:    isLoadedCollectionSelectorFactory('ChatDepartment', 'online')(state),
  chatSelectDepartmentType: chatSelectDepartmentTypeSelector(state),
  chatDefaultDepartment:    chatDefaultDepartmentSelector(state),
  chatRequiredName:         chatRequiredNameSelector(state),
  chatRequiredEmail:        chatRequiredEmailSelector(state),
  widgetLanguage:           widgetLanguageSelector(state),
  loggedIn:                 widgetSessionIsLoginSelector(state),
  defaultValues:            chatFormDefaultValuesSelector(state),
  primaryColor:             primaryColorSelector(state)
}))
export class ChatBeginContainer extends React.Component {

  static propTypes = {
    dispatch:                 PropTypes.func.isRequired,
    children:                 PropTypes.node,
    liveDemo:                 PropTypes.bool,
    customFieldsLoaded:       PropTypes.bool,
    chatDepartmentsLoaded:    PropTypes.bool,
    chatSelectDepartmentType: PropTypes.string,
    chatDefaultDepartment:    PropTypes.number,
    loggedIn:                 PropTypes.bool,
    chatDepartments:          PropTypes.object,
    customFields:             PropTypes.object
  };

  static getWidgetConfig(chatDepartments) {
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
      chatDepartments.map((dep) => {
        if (dep.get('parent') === department.get('id')) {
          choice.children = [];
          rec(dep, choice.children);
        }

        return dep;
      });

      choices.push(choice);
      processed[department.get('id')] = true;
    };

    chatDepartments.map((department) => {
      if (!department.get('parent')) {
        rec(department, config.choices);
      }
      return department;
    });

    return {
      widgetOptions: {
        context:       [parent.document, window.widgetFrame.document],
        contentWindow: window.widgetFrame,
        ownerDocument: window.widgetFrame.document
      },
      config: Immutable.fromJS(config)
    };
  }

  constructor(props) {
    super(props);

    this.state = {
      formData: this.getInitialFormData(props),
      submit:   false,
      errors:   null
    };
  }

  componentDidMount() {
    const { dispatch, liveDemo } = this.props;

    // don't make api calls on live demo
    if (!liveDemo) {
      dispatch(loadAll('CustomDefChat'));
      dispatch(loadAll('ChatDepartment'));
      dispatch(loadWithParams('ChatDepartment', { online: true }, 'online'));
    }

    this.mounted = true;
  }

  componentWillReceiveProps(newProps) {
    const newState = {};
    const { customFields, customFieldsLoaded } = this.props;
    const { chatDepartments, chatDepartmentsLoaded, chatSelectDepartmentType, chatDefaultDepartment } = this.props;

    if ((customFields && !customFields.equals(newProps.customFields))
        || customFieldsLoaded !== newProps.customFieldsLoaded
        || (chatDepartments && !chatDepartments.equals(newProps.chatDepartments))
        || chatSelectDepartmentType !== newProps.chatSelectDepartmentType
        || (!!chatDefaultDepartment && chatDefaultDepartment !== newProps.chatDefaultDepartment)
        || chatDepartmentsLoaded !== newProps.chatDepartmentsLoaded) {
      newState.formData = this.getInitialFormData(newProps);
    }

    this.setState(newState);
  }

  componentDidUpdate() {
    this.formWidget = new PortalFormWidget($(this.node), null, {
      context: [parent.document, window.widgetFrame.document]
    });

    this.formWidget.renderWhenReady();
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  onSubmit = (event) => {
    if (event) {
      event.preventDefault();
    }

    const { liveDemo, dispatch } = this.props;

    // Disabled in live demo mode
    if (liveDemo) {
      return;
    }

    this.setState({
      submit: true,
      banned: false
    });

    const promise = dispatch(createChat(this.state.formData.value));
    promise.then(
      () => {
        history.replace('/chat/active');

        if (this.mounted) {
          this.setState({
            submit: false
          });
        }
      },
      (result) => {
        if (this.mounted) {
          const data = result.getData();
          const newState = {
            submit: false,
            errors: data
          };

          if (data.message === 'banned') {
            newState.banned = true;
          }

          this.setState(newState);
        }
      }
    );
  };

  getInitialFormData(props) {
    const { customFields, defaultValues, children } = props;
    const { chatDepartments, chatSelectDepartmentType, chatDefaultDepartment } = props;
    const formData = {
      name:   defaultValues && defaultValues.get('name') || '',
      email:  defaultValues && defaultValues.get('email') || '',
      fields: {}
    };

    // set default chat department
    if (chatSelectDepartmentType === 'default' && chatDefaultDepartment) {
      if (chatDepartments.has(chatDefaultDepartment)) {
        formData.chat_department = chatDefaultDepartment;
      } else if (chatDepartments.size > 0) {
        formData.chat_department = chatDepartments.first().get('id');
      }
    } else if (children && children.type === ChatBeginSimple && chatDepartments.size > 1) {
      formData.chat_department = chatDepartments.first().get('id');
    } else if (chatDepartments.size > 1) {
      formData.chat_department = '';
    } else if (chatDepartments.size === 1) {
      formData.chat_department = chatDepartments.first().get('id');
    }

    if (defaultValues && defaultValues.get('department') && chatDepartments.has(defaultValues.get('department'))) {
      formData.chat_department = defaultValues.get('department');
    }

    // validate if it's a leaf department
    const leafDepartment = (depId) => {
      const childrenDepartments = chatDepartments.filter(department => department.get('parent') === depId);
      if (childrenDepartments.size > 0) {
        return leafDepartment(childrenDepartments.first().get('id'));
      }

      return depId;
    };

    if (formData.chat_department) {
      formData.chat_department = leafDepartment(formData.chat_department);
    }

    // custom field default values
    customFields.forEach((customField) => {
      const fieldId = customField.get('id');
      const optionsDefaultValue = defaultValues && defaultValues.getIn(['fields', String(fieldId)]);

      let defaultValue = optionsDefaultValue || customField.get('default_value');
      if (defaultValue && defaultValue.toJS) {
        defaultValue = defaultValue.toJS();
      }

      formData.fields[fieldId] = defaultValue;
    });

    return createValue({
      onChange: this.onChange,
      value:    formData
    });
  }

  render() {
    const { loggedIn } = this.props;
    const { customFields, customFieldsLoaded } = this.props;
    const { chatDepartments, chatDepartmentsLoaded, chatSelectDepartmentType } = this.props;
    const allowDepartmentSelection = chatSelectDepartmentType !== 'default' && chatDepartments.size > 1;

    if (!customFieldsLoaded || !chatDepartmentsLoaded) {
      return <ChatBeginLoadingSpinner />;
    }

    let { children } = this.props;

    // if user is logged in and no custom fields and no department selection then force simple chat begin mode
    if (loggedIn && !allowDepartmentSelection && !customFields.size) {
      children = <ChatBeginSimple />;
    }

    const childProps = children.props;

    return (
      <Fieldset formValue={this.state.formData} ref={(c) => { this.node = c; }}>
        {React.cloneElement(children, {
          ...this.props,
          ...childProps,
          ...this.state,

          allowDepartmentSelection,
          onSubmit: this.onSubmit
        })}
      </Fieldset>
    );
  }
}

export default ChatBeginContainer;
