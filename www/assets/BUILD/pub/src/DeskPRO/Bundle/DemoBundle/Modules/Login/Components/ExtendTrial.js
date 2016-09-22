import React, { PropTypes } from 'react';
import { defineMessages, injectIntl, intlShape, FormattedMessage } from 'react-intl';
import Isvg from 'react-inlinesvg';
import { Segment, Segments } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Message } from 'DeskPRO/Component/Semantic/Message';
import { Field, Form, Input, Select, TextArea } from 'DeskPRO/Component/Semantic/Form';

const messages = defineMessages({
  card_holder_placeholder: {
    id:             'cloud.demo_expired.card_holder_placeholder',
    defaultMessage: 'As it appears on the card'
  },
  expiry_month: {
    id:             'cloud.demo_expired.expiry_month',
    defaultMessage: 'MM'
  },
  expiry_year: {
    id:             'cloud.demo_expired.expiry_year',
    defaultMessage: 'YY'
  },
  select_placeholder: {
    id:             'cloud.demo_expired.please_select',
    defaultMessage: 'Please select'
  }
});

class ExtendTrial extends React.Component {
  static propTypes = {
    intl:            intlShape.isRequired,
    country:         PropTypes.string,
    state:           PropTypes.string,
    countries:       PropTypes.object,
    states:          PropTypes.object,
    onSelectCountry: PropTypes.func,
    onChangeState:   PropTypes.func,
    onResumeTrial:   PropTypes.func
  };

  getState = () => {
    const { formatMessage } = this.props.intl;
    const { states, onChangeState } = this.props;

    let input;
    if (this.props.country === 'US') {
      const statesOptions = [];
      for (const code of Object.keys(states)) {
        const label = states[code];
        statesOptions.push({
          value: code,
          label
        });
      }


      input = (
        <Select
          options={statesOptions}
          name="state"
          placeholder={formatMessage(messages.select_placeholder)}
          onChange={onChangeState}
          filter
        />
      );
    } else {
      input = <Input name="state" onChange={onChangeState} value={this.props.state} />;
    }

    return (
      <Field>
        <label htmlFor="state">
          <FormattedMessage
            id="cloud.demo_expired.state"
            defaultMessage="State"
          />
        </label>
        {input}
      </Field>
    );
  };

  render() {
    const { formatMessage } = this.props.intl;
    const { countries } = this.props;

    const countriesOptions = [];
    for (const code of Object.keys(countries)) {
      const label = countries[code];
      countriesOptions.push({
        value: code,
        text:  label,
        label: <span><i className={`flag ${code.toLowerCase()}`} /> {label}</span>
      });
    }


    return (
      <Segment className="extend-trial">
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.extend_trial_title"
            defaultMessage="Extend your trial"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.extend_trial_desc"
            defaultMessage="Get {period} by entering your details below."
            values={{
              period:
                <span className="green">
                  <FormattedMessage
                    id="cloud.demo_expired.extend_trial_period"
                    defaultMessage="7 extra days"
                  />
                </span>
            }}
          />
        </p>
        <Message className="positive">
          <FormattedMessage
            id="cloud.demo_expired.extend_trial_message"
            defaultMessage="You won't pay anything whilst in your trial and you can still cancel at any time."
          />
        </Message>
        <Segments className="horizontal">
          <Segment className="address">
            <Form>
              <h4>
                <i className="icon home" />
                <FormattedMessage
                  id="cloud.demo_expired.billing_address"
                  defaultMessage="Billing address"
                />
              </h4>
              <Field>
                <label htmlFor="address">
                  <FormattedMessage
                    id="cloud.demo_expired.address"
                    defaultMessage="Address"
                  />
                </label>
                <TextArea id="address" rows={2} />
              </Field>
              <Field>
                <label htmlFor="city">
                  <FormattedMessage
                    id="cloud.demo_expired.city"
                    defaultMessage="City"
                  />
                </label>
                <Input id="city" />
              </Field>
              <Field>
                <label htmlFor="zipcode">
                  <FormattedMessage
                    id="cloud.demo_expired.post_code"
                    defaultMessage="Zip / Post Code"
                  />
                </label>
                <Input id="zipcode" />
              </Field>
              {this.getState()}
              <Field>
                <label htmlFor="country">
                  <FormattedMessage
                    id="cloud.demo_expired.country"
                    defaultMessage="Country"
                  />
                </label>
                <Select
                  options={countriesOptions}
                  placeholder={formatMessage(messages.select_placeholder)}
                  onChange={this.props.onSelectCountry}
                  filter
                />
              </Field>
            </Form>
          </Segment>
          <Segment className="credit-card">
            <Form>
              <h4>
                <i className="icon lock" />
                <FormattedMessage
                  id="cloud.demo_expired.credit_card_title"
                  defaultMessage="Credit card details"
                />
              </h4>
              <Field>
                <label htmlFor="card_holder">
                  <FormattedMessage
                    id="cloud.demo_expired.card_holder"
                    defaultMessage="Card holder name"
                  />
                </label>
                <Input
                  id="card_holder"
                  placeholder={formatMessage(messages.card_holder_placeholder)}
                />
              </Field>
              <Field className="card-number">
                <label htmlFor="card_number">
                  <FormattedMessage
                    id="cloud.demo_expired.card_number"
                    defaultMessage="Card number"
                  />
                  <Isvg
                    src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/visa.svg`}
                  />
                  <Isvg
                    src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/mastercard.svg`}
                  />
                  <Isvg
                    src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/amex.svg`}
                  />
                </label>
                <Input id="card_number" type="number" />
              </Field>
              <Field className="expiry">
                <label htmlFor="expiry_month">Expiry</label>
                <Input
                  id="expiry_month"
                  placeholder={formatMessage(messages.expiry_month)}
                  type="number"
                />
                <span> / </span>
                <Input
                  id="expiry_year"
                  placeholder={formatMessage(messages.expiry_year)}
                  type="number"
                />
              </Field>
              <Field className="security-code">
                <label htmlFor="security_code">
                  <FormattedMessage
                    id="cloud.demo_expired.security_code"
                    defaultMessage="Security code"
                  />
                </label>
                <Input id="security_code" type="number" />
                <img
                  src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/backofcard.png`}
                  alt="Back of card"
                />
              </Field>
              <Button onClick={this.props.onResumeTrial} className="positive">
                <FormattedMessage
                  id="cloud.demo_expired.extend_trial_resume"
                  defaultMessage="Resume free trial"
                />
              </Button>
            </Form>
          </Segment>
        </Segments>
      </Segment>
    );
  }
}
export default injectIntl(ExtendTrial);
