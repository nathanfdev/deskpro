import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import { getFormDataErrors } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';

class AccountForm extends React.Component {

  static propTypes = {
    account:           PropTypes.object,
    onSubmit:          PropTypes.func,
    onTestCredentials: PropTypes.func,
    onDeleteAccount:   PropTypes.func,
    saving:            PropTypes.bool,
    testing:           PropTypes.bool,
    deleting:          PropTypes.bool,
    displaySuccess:    PropTypes.bool,  // eslint-disable-line react/no-unused-prop-types
    errors:            PropTypes.object // eslint-disable-line react/no-unused-prop-types
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value: {
          account_name: props.account ? props.account.get('account_name') : '',
          account_id:   props.account ? props.account.get('account_id') : '',
          auth_token:   props.account ? props.account.get('auth_token') : ''
        },
        errorList: {},
        onChange:  this.onChange
      }),
      displaySuccess: false
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      formData: createValue({
        value:     this.state.formData.value,
        errorList: nextProps.errors,
        onChange:  this.onChange
      }),
      displaySuccess: nextProps.displaySuccess
    });
  }

  onChange = (formData) => {
    this.setState({ formData, displaySuccess: false });
  };

  onSubmit = (event) => {
    event.preventDefault();
    this.props.onSubmit(this.state.formData.value);
  };

  onTestCredentials = (event) => {
    event.preventDefault();
    this.props.onTestCredentials(this.state.formData.value);
  };

  onDeleteAccount = (event) => {
    event.preventDefault();

    const { account, onDeleteAccount } = this.props;
    onDeleteAccount(account);
  };

  renderCloud() {
    const { saving, testing, deleting } = this.props;
    const { formData } = this.state;

    const formErrors = getFormDataErrors(formData);
    const fundError = formErrors ? formErrors.filter(e => e.code === 'dpms_client.no_funds') : null;

    return fundError ? (
      <div>
        Voice requires your account to be in credit with auto-topup enabled. This can be done from your billing area.
        <br /><br />
        <a href="#/license" className={classNames('ui button')}>
          Enable Auto-Topup from the Billing Area &rarr;
        </a>
      </div>
    ) : (
      <Form onSubmit={this.onSubmit} formValue={formData}>
        <Fieldset>
          <p>
            Deskpro Voice allows your agents to make and accept phone calls. Rent phone numbers in a wide range of countries,
            set up queues and call trees, accept voicemail, and more.
            <a href="https://www.deskpro.com/product/voice/" target="_blank" rel="noopener noreferrer">Click here to read more about voice</a>.
          </p>

          <br /><br />

          <button className={classNames('ui button', { loading: saving, disabled: testing || deleting })}>
            Enable Voice
          </button>
        </Fieldset>
      </Form>
    );
  }

  render() {
    if (window.DP_IS_CLOUD) {
      return this.renderCloud();
    }

    const { saving, testing, deleting, account } = this.props;
    const { formData, displaySuccess } = this.state;

    return (
      <Form onSubmit={this.onSubmit} formValue={formData}>
        <Fieldset>
          {account &&
            <Field select="account_name" label="Account Name">
              <Input placeholder="Account Name" />
            </Field>
          }
          <Field select="account_id" label="Account SID">
            <Input placeholder="Account SID" />
          </Field>
          <Field select="auth_token" label="Auth Token">
            <Input placeholder="Auth Token" />
          </Field>

          <button className={classNames('ui button', { loading: saving, disabled: testing || deleting })}>
            {account ? 'Update account' : 'Create account'}
          </button>
          <button
            className={classNames('ui button', { loading: testing, disabled: saving || deleting })}
            onClick={this.onTestCredentials}
          >
            Test settings
          </button>

          {account &&
            <span
              className={classNames('voice-delete-button', { disabled: deleting || saving || testing })}
              onClick={this.onDeleteAccount}
            >
              Delete this account
            </span>}

          {displaySuccess &&
            <div className="ui positive message">
              Your settings are correct
            </div>
          }
        </Fieldset>
      </Form>
    );
  }
}

export default AccountForm;
