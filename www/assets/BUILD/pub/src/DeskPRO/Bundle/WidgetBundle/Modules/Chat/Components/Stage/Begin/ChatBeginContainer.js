import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Fieldset, createValue } from 'react-forms';
import $ from 'jquery';
import Immutable from 'immutable';
import { loadAll, isLoadedCollectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import PortalFormWidget from 'DeskPRO/Bundle/PortalBundle/PageWidget/PortalFormWidget';
import { createChat } from '../../../Actions/chatActions';
import { liveDemoSelector, widgetAllowDepartmentSelection, widgetLanguageSelector, chatFormDefaultValuesSelector } from '../../../../Application/Selectors/dpWindow';
import { requireChatEmailValidationSelector, requireChatLoginSelector, widgetSessionIsLoginSelector } from '../../../../Application/Selectors/bootstrap';
import { customChatFieldsOrderedSelector } from '../../../../Application/Selectors/customFields';
import { history } from '../../../../../Services/history';
import { ChatBeginLoadingSpinner } from './ChatBeginLoadingSpinner';
import { ChatBeginSimple } from './ChatBeginSimple';

@connect(state => ({
  liveDemo:                 liveDemoSelector(state),
  requireEmailValidation:   requireChatEmailValidationSelector(state),
  requireLogin:             requireChatLoginSelector(state),
  customFieldsLoaded:       isLoadedCollectionSelectorFactory('CustomDefChat', 'all')(state),
  customFields:             customChatFieldsOrderedSelector(state),
  allowDepartmentSelection: widgetAllowDepartmentSelection(state),
  chatDepartments:          allSelectorFactory('ChatDepartment')(state),
  chatDepartmentsLoaded:    isLoadedCollectionSelectorFactory('ChatDepartment', 'all')(state),
  widgetLanguage:           widgetLanguageSelector(state),
  loggedIn:                 widgetSessionIsLoginSelector(state),
  defaultValues:            chatFormDefaultValuesSelector(state)
}))
export class ChatBeginContainer extends React.Component {

  static propTypes = {
    requireEmailValidation:   PropTypes.bool,
    requireLogin:             PropTypes.bool,
    dispatch:                 PropTypes.func.isRequired,
    children:                 PropTypes.node,
    liveDemo:                 PropTypes.bool,
    allowDepartmentSelection: PropTypes.bool,
    customFieldsLoaded:       PropTypes.bool,
    chatDepartmentsLoaded:    PropTypes.bool,
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
    const { allowDepartmentSelection, dispatch } = this.props;

    dispatch(loadAll('CustomDefChat'));

    if (allowDepartmentSelection) {
      dispatch(loadAll('ChatDepartment'));
    }

    this.mounted = true;
  }

  componentWillReceiveProps(newProps) {
    const newState = {};
    const { children, customFields, customFieldsLoaded } = this.props;
    const { chatDepartments, chatDepartmentsLoaded, allowDepartmentSelection } = this.props;

    if (children !== newProps.children
        || customFields !== newProps.customFields
        || customFieldsLoaded !== newProps.customFieldsLoaded
        || allowDepartmentSelection !== newProps.allowDepartmentSelection
        || chatDepartments !== newProps.chatDepartments
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

    const { liveDemo, requireEmailValidation, requireLogin, dispatch } = this.props;

    // Disabled in live demo mode
    if (liveDemo) {
      return;
    }

    this.setState({
      submit: true
    });

    const promise = dispatch(createChat(this.state.formData.value));
    promise.then(
      () => {
        if (requireEmailValidation && !requireLogin) {
          history.replace('/chat/validation/email');
        } else {
          history.replace('/chat/active');
        }

        if (this.mounted) {
          this.setState({
            submit: false
          });
        }
      },
      (result) => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: result.getData()
          });
        }
      }
    );
  };

  getInitialFormData(props) {
    const { children, customFields, allowDepartmentSelection, defaultValues } = props;
    const formData = {
      name:   '',
      email:  '',
      fields: {}
    };

    if (children.type !== ChatBeginSimple) {
      if (allowDepartmentSelection) {
        formData.chat_department = '';
      }

      customFields.forEach((customField) => {
        const fieldId = customField.get('id');
        const optionsDefaultValue = defaultValues && defaultValues.getIn(['fields', String(fieldId)]);

        let defaultValue = optionsDefaultValue || customField.get('default_value');
        if (defaultValue && defaultValue.toJS) {
          defaultValue = defaultValue.toJS();
        }

        formData.fields[fieldId] = defaultValue;
      });
    }

    return createValue({
      onChange: this.onChange,
      value:    formData
    });
  }

  render() {
    const { loggedIn } = this.props;
    const { customFields, customFieldsLoaded } = this.props;
    const { allowDepartmentSelection, chatDepartments, chatDepartmentsLoaded } = this.props;

    if (!customFieldsLoaded || (allowDepartmentSelection && !chatDepartmentsLoaded)) {
      return <ChatBeginLoadingSpinner />;
    }

    let { children } = this.props;

    // if user is logged in and no custom fields and no department selection then force simple chat begin mode
    if (loggedIn && !(chatDepartments.size && allowDepartmentSelection) && !customFields.size) {
      children = <ChatBeginSimple />;
    }

    const childProps = children.props;

    return (
      <Fieldset formValue={this.state.formData} ref={(c) => { this.node = c; }}>
        {React.cloneElement(children, {
          ...this.props,
          ...childProps,
          ...this.state,

          onSubmit: this.onSubmit
        })}
      </Fieldset>
    );
  }
}

export default ChatBeginContainer;
