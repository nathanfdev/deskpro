import React, { PropTypes } from 'react';
import { Fieldset, Input, createValue } from 'react-forms';
import { Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
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
    deleting:          PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value: {
          account_name: props.account ? props.account.get('account_name') : '',
          account_sid:  props.account ? props.account.get('account_sid') : '',
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

  render() {
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
          <Field select="account_sid" label="Account SID">
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
            <button
              className={classNames('ui right floated red button', { loading: deleting, disabled: saving || testing })}
              onClick={this.onDeleteAccount}
            >
              Delete account
            </button>}

          {displaySuccess &&
            <div className="ui positive message">
              Your settings are correct
            </div>
          }
          {getFormDataErrors(formData) &&
            <div className="ui negative message">
              <ul>
                {getFormDataErrors(formData).map((error, index) =>
                  <li key={index}>{error.message}</li>
                )}
              </ul>
            </div>
          }
        </Fieldset>
      </Form>
    );
  }
}

export default AccountForm;
