import React, { PropTypes } from 'react';
import { defineMessages, injectIntl, intlShape, FormattedMessage } from 'react-intl';
import Isvg from 'react-inlinesvg';
import classNames from 'classnames';
import { Segment, Segments } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Message } from 'DeskPRO/Component/Semantic/Message';
import * as card from 'DeskPRO/Component/Form/Card';
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

@injectIntl
class ExtendTrial extends React.Component {
  static propTypes = {
    intl:                 intlShape.isRequired,
    address:              PropTypes.string,
    city:                 PropTypes.string,
    postCode:             PropTypes.string,
    state:                PropTypes.string,
    country:              PropTypes.string,
    vatId:                PropTypes.string,
    schedule:             PropTypes.string,
    cardName:             PropTypes.string,
    cardNumber:           PropTypes.string,
    expiryMonth:          PropTypes.string,
    expiryYear:           PropTypes.string,
    securityCode:         PropTypes.string,
    states:               PropTypes.object,
    countries:            PropTypes.object,
    euCountries:          PropTypes.array,
    errors:               PropTypes.object,
    submit:               PropTypes.bool,
    onChangeAddress:      PropTypes.func,
    onChangeCity:         PropTypes.func,
    onChangePostCode:     PropTypes.func,
    onChangeState:        PropTypes.func,
    onChangeCountry:      PropTypes.func,
    onChangeVatId:        PropTypes.func,
    onChangeSchedule:     PropTypes.func,
    onChangeCardName:     PropTypes.func,
    onChangeCardNumber:   PropTypes.func,
    onChangeExpiryMonth:  PropTypes.func,
    onChangeExpiryYear:   PropTypes.func,
    onChangeSecurityCode: PropTypes.func,
    onResumeTrial:        PropTypes.func,
    onDeleteAccount:      PropTypes.func
  };
  static defaultProps = {
    cardNumber: ''
  };

  getState = () => {
    const { formatMessage } = this.props.intl;
    const { states, country, onChangeState } = this.props;

    let input;
    if (country === 'US') {
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
          id="state"
          placeholder={formatMessage(messages.select_placeholder)}
          onChange={onChangeState}
          value={this.props.state}
          filter
        />
      );
    } else {
      input = <Input id="state" name="state" onChange={onChangeState} value={this.props.state} />;
    }

    return (
      <Field field="state" errors={this.props.errors}>
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

  getVatId = () => {
    const { euCountries, country, vatId, onChangeVatId } = this.props;
    if (euCountries.indexOf(country) === -1) {
      return null;
    }
    return (
      <Field field="vat_id" errors={this.props.errors}>
        <label htmlFor="vat_id">
          <FormattedMessage
            id="cloud.demo_expired.vat_id"
            defaultMessage="VAT ID (optionnal)"
          />
        </label>
        <Input name="vat_id" onChange={onChangeVatId} value={vatId} />
      </Field>
    );
  };

  getError = () => {
    if (this.props.errors && this.props.errors.message) {
      return (
        <Message className="negative">
          {this.props.errors.message}
        </Message>
      );
    }
    return null;
  };

  render() {
    const { formatMessage } = this.props.intl;
    const { countries, cardNumber } = this.props;

    const countriesOptions = [];
    for (const code of Object.keys(countries)) {
      const label = countries[code];
      countriesOptions.push({
        value: code,
        text:  label,
        label: <span><i className={`flag ${code.toLowerCase()}`} /> {label}</span>
      });
    }

    const schedules = [
      {
        value: 'monthly',
        label: 'Monthly'
      },
      {
        value: 'yearly',
        label: 'Yearly'
      }
    ];


    return (
      <Segments className="extend-trial horizontal">
        <Segment className="extend-trial-form">
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
          {this.getError()}
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
                <Field field="address" errors={this.props.errors}>
                  <label htmlFor="address">
                    <FormattedMessage
                      id="cloud.demo_expired.address"
                      defaultMessage="Address"
                    />
                  </label>
                  <TextArea
                    id="address"
                    name="address"
                    onChange={this.props.onChangeAddress}
                    value={this.props.address}
                    rows={2}
                  />
                </Field>
                <Field field="city" errors={this.props.errors}>
                  <label htmlFor="city">
                    <FormattedMessage
                      id="cloud.demo_expired.city"
                      defaultMessage="City"
                    />
                  </label>
                  <Input
                    id="city"
                    onChange={this.props.onChangeCity}
                    value={this.props.city}
                  />
                </Field>
                <Field field="postcode" errors={this.props.errors}>
                  <label htmlFor="postcode">
                    <FormattedMessage
                      id="cloud.demo_expired.post_code"
                      defaultMessage="Zip / Post Code"
                    />
                  </label>
                  <Input
                    id="postcode"
                    onChange={this.props.onChangePostCode}
                    value={this.props.postCode}
                  />
                </Field>
                {this.getState()}
                <Field field="country" errors={this.props.errors}>
                  <label htmlFor="country">
                    <FormattedMessage
                      id="cloud.demo_expired.country"
                      defaultMessage="Country"
                    />
                  </label>
                  <Select
                    options={countriesOptions}
                    placeholder={formatMessage(messages.select_placeholder)}
                    onChange={this.props.onChangeCountry}
                    value={this.props.country}
                    filter
                  />
                </Field>
                {this.getVatId()}
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
                <Field field="schedule" errors={this.props.errors}>
                  <label htmlFor="schedule">
                    <FormattedMessage
                      id="cloud.demo_expired.schedule"
                      defaultMessage="Schedule"
                    />
                  </label>
                  <Select
                    options={schedules}
                    placeholder={formatMessage(messages.select_placeholder)}
                    onChange={this.props.onChangeSchedule}
                    value={this.props.schedule}
                  />
                </Field>
                <Field field="card_name" errors={this.props.errors}>
                  <label htmlFor="card_holder">
                    <FormattedMessage
                      id="cloud.demo_expired.card_holder"
                      defaultMessage="Card holder name"
                    />
                  </label>
                  <Input
                    id="card_holder"
                    onChange={this.props.onChangeCardName}
                    value={this.props.cardName}
                    placeholder={formatMessage(messages.card_holder_placeholder)}
                  />
                </Field>
                <Field className="card-number" field="card_number" errors={this.props.errors}>
                  <label htmlFor="card_number">
                    <FormattedMessage
                      id="cloud.demo_expired.card_number"
                      defaultMessage="Card number"
                    />
                    <Isvg
                      className={classNames({ faded: (card.isCardAmex(cardNumber) || card.isCardMasterCard(cardNumber)) })}
                      src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/visa.svg`}
                    />
                    <Isvg
                      className={classNames({ faded: (card.isCardVisa(cardNumber) || card.isCardAmex(cardNumber)) })}
                      src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/mastercard.svg`}
                    />
                    <Isvg
                      className={classNames({ faded: (card.isCardMasterCard(cardNumber) || card.isCardVisa(cardNumber)) })}
                      src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/amex.svg`}
                    />
                  </label>
                  <Input
                    id="card_number"
                    onChange={this.props.onChangeCardNumber}
                    value={card.formatCardNumber(this.props.cardNumber)}
                    type="text"
                  />
                </Field>
                <Field className="expiry" field="card_expiry" errors={this.props.errors}>
                  <label htmlFor="expiry_month">Expiry</label>
                  <Input
                    id="expiry_month"
                    onChange={this.props.onChangeExpiryMonth}
                    value={this.props.expiryMonth}
                    placeholder={formatMessage(messages.expiry_month)}
                    type="number"
                  />
                  <span> / </span>
                  <Input
                    id="expiry_year"
                    onChange={this.props.onChangeExpiryYear}
                    value={this.props.expiryYear}
                    placeholder={formatMessage(messages.expiry_year)}
                    type="number"
                  />
                </Field>
                <Field className="security-code" field="security_code" errors={this.props.errors}>
                  <label htmlFor="security_code">
                    <FormattedMessage
                      id="cloud.demo_expired.security_code"
                      defaultMessage="Security code"
                    />
                  </label>
                  <Input
                    id="security_code"
                    onChange={this.props.onChangeSecurityCode}
                    value={this.props.securityCode}
                    type="number"
                  />
                  <img
                    src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/backofcard.png`}
                    alt="Back of card"
                  />
                </Field>
                <Button
                  onClick={this.props.onResumeTrial}
                  className={classNames('positive', { loading: this.props.submit })}
                >
                  <FormattedMessage
                    id="cloud.demo_expired.extend_trial_resume"
                    defaultMessage="Resume free trial"
                  />
                </Button>
              </Form>
            </Segment>
          </Segments>
        </Segment>
        <Segment className="extend-trial-side-bar">
          <div className="offer">
            <div className="intro">
              <FormattedMessage
                id="cloud.demo_expired.offer_text"
                defaultMessage="One plan and one price, with all features for everyone."
              />
            </div>
            <div className="trophy">
              <i className="icon trophy" />
            </div>
            <div className="price">$30</div>
            <div className="unit">
              <FormattedMessage
                id="cloud.demo_expired.offer_unit"
                defaultMessage="per agent/month"
              />
            </div>
          </div>
          <Button className="questions">
            <i className="icon comments outline" />
            <FormattedMessage
              id="cloud.demo_expired.got_questions"
              defaultMessage="Got questions? Just ask..."
            />
          </Button>
          <Button className="delete secondary" onClick={this.props.onDeleteAccount}>
            <FormattedMessage
              id="cloud.demo_expired.delete"
              defaultMessage="Delete your account"
            />
          </Button>
        </Segment>
      </Segments>
    );
  }
}
export default ExtendTrial;
