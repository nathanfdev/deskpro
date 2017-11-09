import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import classNames from 'classnames';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Form, Field, Input, Checkbox, Radio, ClipboardInput, Select } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';

const grantTypes = [
  { value: 'authorization_code', label: 'Authorization Code Grant (e.g. server-side with secrets)' },
  { value: 'token', label: 'Implicit Grant (e.g. client side like browsers or mobile apps)' }
];

class OAuthClientForm extends BaseForm {

  static propTypes = {
    client:       PropTypes.object,
    onReturnBack: PropTypes.func,
    onDelete:     PropTypes.func
  };

  onCancel = (event) => {
    event.preventDefault();
    this.props.onCancel();
  };

  onDelete = (event) => {
    event.preventDefault();
    this.props.onDelete();
  };

  getDefaultState() {
    const { client } = this.props;

    return {
      auth_endpoint:      client ? client.get('auth_endpoint') : '',
      token_endpoint:     client ? client.get('token_endpoint') : '',
      public_id:          client ? client.get('public_id') : '',
      secret:             client ? client.get('secret') : '',
      name:               client ? client.get('name') : '',
      redirect_uris:      client ? client.get('redirect_uris').toJS() : [''],
      is_enabled:         client ? client.get('is_enabled') : true,
      context:            client ? client.get('context') : 'user',
      allowed_grant_type: client ? client.get('allowed_grant_type') : 'authorization_code'
    };
  }

  transformSubmitData(submitData) { // eslint-disable-line
    delete submitData.public_id;
    delete submitData.secret;
    delete submitData.auth_endpoint;
    delete submitData.token_endpoint;

    return submitData;
  }

  render() {
    const { client, onReturnBack } = this.props;
    const { formData, saving } = this.state;
    const isBuiltIn = client && client.get('sys_name');
    const isAuthCode = client && client.get('allowed_grant_type') === 'authorization_code';

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title={client ? 'Update client' : 'Create new client'} dividing />

        <div className="twilio-queue-form">
          <Form onSubmit={this.onSubmit} formValue={formData}>
            <Fieldset>
              <Field select="public_id" label="Client ID">
                <ClipboardInput type="text" disabled />
              </Field>
              {isAuthCode &&
              <Field select="secret" label="Client Secret">
                <ClipboardInput type="text" disabled />
              </Field>}
              {client &&
              <Field
                select="auth_endpoint"
                label="Auth Endpoint"
                help="Login page URL."
              >
                <ClipboardInput type="text" disabled />
              </Field>}
              {isAuthCode &&
              <Field
                select="token_endpoint"
                label="Token Endpoint"
                help="API endpoint to get access token by OAuth authorization code."
              >
                <ClipboardInput type="text" disabled />
              </Field>}

              <Field select="context" label="Context">
                <Context disabled={!!client} />
              </Field>
              <Field select="allowed_grant_type" label="Grant Type">
                <Select choices={grantTypes} disabled={!!client} />
              </Field>
              <Field
                select="name"
                label="Client name"
                help="Enter any arbitrary name or description. This is used for your own records only."
              >
                <Input type="text" disabled={isBuiltIn} />
              </Field>
              <Field
                select="redirect_uris"
                label="Authorized redirect URIs"
                help="For use with requests from a web server.
                This is the path in your application that users are redirected
                to after they have authenticated with helpdesk.
                The path will be appended with the authorization code for access.
                Must have a protocol. Cannot contain URL fragments or relative paths.
                You can set just the url's domain, the first part of the url or several redirect urls.
                Then you need to add 'redirect_uri' parameter to your auth endpoint url."
              >
                <RedirectUrls disabled={isBuiltIn} />
              </Field>
              <Field select="is_enabled">
                <Checkbox label="Is enabled" />
              </Field>

              <button className={classNames('ui button', { loading: saving })}>
                {client ? 'Update' : 'Create'}
              </button>
              <button
                className={classNames('ui basic button cancel-button', { disabled: saving })}
                onClick={this.onCancel}
              >
                Cancel
              </button>

              {client && !isBuiltIn &&
              <span className="voice-delete-button" onClick={this.onDelete}>
                Delete this client
              </span>}
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

class Context extends React.Component {

  static propTypes = {
    value:    PropTypes.array,
    onChange: PropTypes.func,
    disabled: PropTypes.bool
  };

  render() {
    const { value, disabled, onChange } = this.props;

    return (
      <div className="inline fields">
        <div className="field">
          <Radio
            name="context"
            label="User"
            choice="user"
            value={value}
            disabled={disabled}
            onChange={onChange}
          />
        </div>
        <div className="field">
          <Radio
            name="context"
            label="Agent"
            choice="agent"
            value={value}
            disabled={disabled}
            onChange={onChange}
          />
        </div>
      </div>
    );
  }
}

class RedirectUrls extends React.Component {

  static propTypes = {
    value:    PropTypes.array,
    onChange: PropTypes.func,
    disabled: PropTypes.bool
  };

  addAnotherUrl = (event) => {
    event.preventDefault();
    const { value, onChange } = this.props;

    value.push('');
    onChange(value);
  };

  removeUrl = (index) => {
    const { value, onChange } = this.props;

    value.splice(index, 1);
    onChange(value);
  };

  render() {
    const { value, disabled } = this.props;

    return (
      <div>
        {value.map((url, index) =>
          <Field key={index} select={String(index)}>
            <RedirectUrl
              onDelete={value.length > 1 ? () => { this.removeUrl(index); } : null}
              disabled={disabled}
            />
          </Field>
        )}
        {!disabled &&
        <a style={{ cursor: 'pointer' }} onClick={this.addAnotherUrl}>
          {value.length ? 'Add another url' : 'Add url'}
        </a>}
      </div>

    );
  }
}

class RedirectUrl extends React.Component {

  static propTypes = {
    value:    PropTypes.array,
    onChange: PropTypes.func,
    onDelete: PropTypes.func,
    disabled: PropTypes.bool
  };

  render() {
    const { value, disabled, onChange, onDelete } = this.props;

    return (
      <div className="ui icon input">
        <Input value={value} onChange={onChange} disabled={disabled} />
        {!disabled && onDelete && <i className="remove link icon" onClick={onDelete} />}
      </div>
    );
  }
}

export default OAuthClientForm;
