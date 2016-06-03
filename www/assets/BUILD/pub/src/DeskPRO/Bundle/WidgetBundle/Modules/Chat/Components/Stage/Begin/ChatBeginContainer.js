import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { createChat } from '../../../Actions/chatActions';
import { liveDemoSelector } from '../../../../Application/Selectors/dpWindow';
import { requireChatEmailValidationSelector, requireChatLoginSelector } from '../../../../Application/Selectors/bootstrap';
import { customChatFieldsOrderedSelector } from '../../../../Application/Selectors/customFields';
import { history } from '../../../../../Services/history';
import { loadAll, isLoadedCollectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { Fieldset, createValue } from 'react-forms';
import { PortalFormWidget } from 'DeskPRO/Bundle/PortalBundle/PageWidget/PortalFormWidget';
import $ from 'jquery';

@connect(state => ({
  liveDemo:               liveDemoSelector(state),
  requireEmailValidation: requireChatEmailValidationSelector(state),
  requireLogin:           requireChatLoginSelector(state),
  customFieldsLoaded:     isLoadedCollectionSelectorFactory('CustomDefChat', 'all')(state),
  customFields:           customChatFieldsOrderedSelector(state)
}))
export class ChatBeginContainer extends React.Component {

  static propTypes = {
    requireEmailValidation: PropTypes.bool,
    requireLogin:           PropTypes.bool,
    dispatch:               PropTypes.func.isRequired,
    children:               PropTypes.node,
    isCreated:              PropTypes.bool,
    liveDemo:               PropTypes.bool,
    customFields:           PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      formData: this.getInitialFormData(),
      submit:   false,
      errors:   null
    };
  }

  componentDidMount() {
    this.props.dispatch(loadAll('CustomDefChat'));
    this.mounted = true;
  }

  componentWillReceiveProps(newProps) {
    const newState = {};
    if (newProps.children !== this.props.children) {
      newState.formData = this.getInitialFormData();
    }

    this.setState(newState);
  }

  componentDidUpdate() {
    this.formWidget = new PortalFormWidget($(ReactDOM.findDOMNode(this)), null, {
      context: [parent.document, window.widgetFrame.document]
    });

    this.formWidget.renderWhenReady();
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onChange = formData => {
    this.setState({ formData });
  };

  onSubmit = event => {
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
      result => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: result.getData()
          });
        }
      }
    );
  };

  getInitialFormData() {
    return createValue({
      onChange: this.onChange,
      value:    {
        name:  '',
        email: ''
      }
    });
  }

  render() {
    const { children } = this.props;
    const childProps = children.props;

    return (
      <Fieldset formValue={this.state.formData}>
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
