import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import { Field, Input, DatePicker } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseSource from './BaseSource';

class Zendesk extends BaseSource {

  getFormFields = () => (
    <div>
      <Fieldset className="field" select="account">
        <Field select="subdomain" label="Domain">
          <Subdomain />
        </Field>
        <Field select="username" label="Admin Username">
          <Input type="text" placeholder="email@example.com" />
        </Field>
        <Field select="token" label="Admin Token">
          <Token />
        </Field>
      </Fieldset>
      <Field select="start_time" label="Start Time">
        <DatePicker />
      </Field>
      <Field select="ticket_brand_field" label="Ticket Brand Custom Field">
        <Input type="text" placeholder="Brand" />
      </Field>
    </div>
  );
}

class Subdomain extends React.Component {

  render() {
    return (
      <div>
        <Input type="text" placeholder="yoursubdomain" {...this.props} />
        <span className="field-info">
          .zendesk.com
        </span>
      </div>
    );
  }
}

class Token extends React.Component {

  render() {
    return (
      <div>
        <Input type="text" placeholder="U3YxdX8gXuGuCWv1tdUSj8VbfdWHyf3gLnderFOk" {...this.props} />
        <span className="field-info">
          (<a
            href="https://support.deskpro.com/en_GB/guides/admin-guide/importing-data/using-the-full-import-tool"
            target="_blank"
            rel="noopener noreferrer"
          >
            Click here for instructions
          </a>)
        </span>
      </div>
    );
  }
}

export default Zendesk;
