import React, { PropTypes } from 'react';
import Isvg from 'react-inlinesvg';
import { Segment, Segments } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Message } from 'DeskPRO/Component/Semantic/Message';
import { Field, Form, Input, TextArea } from 'DeskPRO/Component/Semantic/Form';
import amexSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/amex.svg';
import masterCardSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/mastercard.svg';
import visaSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/visa.svg';
import cardBackPng from 'DeskPRO/Bundle/DemoBundle/Resources/img/backofcard.png';

export class ExtendTrialContainer extends React.Component {
  render() {
    return <ExtendTrial />;
  }
}

export class ExtendTrial extends React.Component {
  static propTypes = {
    onResumeTrial:   PropTypes.func,
    onDeleteAccount: PropTypes.func
  };

  render() {
    return (
      <Segment classes="extend-trial">
        <h3>Extend your trial</h3>
        <p>Get <span className="green">7 extra days</span> by entering your details below.</p>
        <Message classes="positive">
          You won't pay anything whilst in your trial and you can still cancel at any time.
        </Message>
        <Segments classes="horizontal">
          <Segment classes="address">
            <Form>
              <h4>
                <i className="icon home" />
                Billing address
              </h4>
              <Field>
                <label htmlFor="address">Address</label>
                <TextArea id="address" rows={2} />
              </Field>
              <Field>
                <label htmlFor="city">City</label>
                <Input id="city" />
              </Field>
              <Field>
                <label htmlFor="zipcode">Zip / Post Code</label>
                <Input id="zipcode" />
              </Field>
              <Field>
                <label htmlFor="state">State</label>
                <Input id="state" />
              </Field>
              <Field>
                <label htmlFor="country">Country</label>
                <Input id="country" />
              </Field>
            </Form>
          </Segment>
          <Segment classes="credit-card">
            <Form>
              <h4>
                <i className="icon lock" />
                Credit card details
              </h4>
              <Field>
                <label htmlFor="card_holder">Card holder name</label>
                <Input id="card_holder" placeholder="As it appears on the card" />
              </Field>
              <Field classes="card-number">
                <label htmlFor="card_number">
                  Card number
                  <Isvg src={visaSvg} />
                  <Isvg src={masterCardSvg} />
                  <Isvg src={amexSvg} />
                </label>
                <Input id="card_number" type="number" />
              </Field>
              <Field classes="expiry">
                <label htmlFor="expiry_month">Expiry</label>
                <Input id="expiry_month" placeholder="MM" type="number" />
                <span> / </span>
                <Input id="expiry_year" placeholder="YY" type="number" />
              </Field>
              <Field classes="security-code">
                <label htmlFor="security_code">
                  Security code
                </label>
                <Input id="security_code" type="number" />
                <img src={cardBackPng} alt="Back of card" />
              </Field>
              <Button onClick={this.props.onResumeTrial} classes="positive">Resume free trial</Button>
            </Form>
          </Segment>
        </Segments>
      </Segment>
    );
  }
}
