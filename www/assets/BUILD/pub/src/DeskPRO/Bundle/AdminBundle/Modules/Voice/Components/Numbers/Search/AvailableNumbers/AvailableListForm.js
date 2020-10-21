import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, BlurInput, Select, CountryCodeSelect, Radio } from 'DeskPRO/Component/Semantic/ReactForm';
import AccountChoiceWrapper from '../../../Common/AccountChoiceWrapper';

const defaultCountryCodes = [
  'US',
  'AU',
  'AT',
  'BE',
  'BR',
  'BG',
  'CA',
  'CL',
  'CY',
  'DK',
  'DO',
  'SV',
  'EE',
  'FI',
  'FR',
  'DE',
  'GR',
  'ID',
  'IE',
  'IL',
  'IT',
  'JP',
  'LV',
  'LT',
  'LU',
  'MT',
  'NL',
  'NZ',
  'PE',
  'PL',
  'PT',
  'PR',
  'RO',
  'SK',
  'ZA',
  'ES',
  'SE',
  'CH',
  'TW',
  'GB'
];

const countryCodesWithRegions = ['US', 'CA', 'TW'];
const numberTypes = {
  local:    'Local',
  tollfree: 'Toll free',
  mobile:   'Mobile',
  national: 'National',
  fixed:    'Fixed'
};

class AvailableListForm extends React.Component {

  static propTypes = {
    value:                 PropTypes.number,
    accounts:              PropTypes.object,
    availableCountries:    PropTypes.object,
    availableNumbersTypes: PropTypes.object,
    onChange:              PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value:    props.value,
        onChange: this.onChange
      })
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      formData: createValue({
        value:    nextProps.value,
        onChange: this.onChange
      })
    });
  }

  onChange = (formData, fields) => {
    if (fields.indexOf('country_code') !== -1) {
      const { accounts, availableNumbersTypes = {} } = this.props;
      const account = accounts.get(formData.value.account);

      formData.value.type = null;
      if (account) {
        formData.value.type = 'mobile';
        if (account.get('type') === 'twilio' && formData.value.country_code) {
          const allowedTypes = availableNumbersTypes[formData.value.country_code];
          if (allowedTypes && allowedTypes.indexOf('mobile') === -1) {
            formData.value.type = allowedTypes[0];
          }
        }
      }
    }

    this.setState({ formData });
    this.props.onChange(formData.value);
  };

  render() {
    const { accounts = Immutable.fromJS([]), availableCountries, availableNumbersTypes = {} } = this.props;
    const { formData } = this.state;
    const account = accounts.get(formData.value.account);
    const countryCode = formData.value.country_code;

    let accountCountryCodes = defaultCountryCodes;
    if (availableCountries && availableCountries[account.get('id')]) {
      const availableCountryCodes = availableCountries[account.get('id')].map(availableCountry => availableCountry.country_code);
      accountCountryCodes = accountCountryCodes.filter(accountCountryCode => availableCountryCodes.indexOf(accountCountryCode) !== -1);
    }

    let availableTypes = [];
    if (account) {
      if (countryCode && availableNumbersTypes[countryCode]) {
        availableTypes = [...availableNumbersTypes[countryCode]];
      }

      // no numbers with national type, so just don't display this option at all
      const nationalIndex = availableTypes.indexOf('national');
      if (nationalIndex !== -1) {
        availableTypes.splice(nationalIndex, 1);
      }
    }

    return (
      <div className="twilio-number-search-form">
        <Form formValue={formData}>
          <Fieldset>
            {accounts.size > 1 &&
              <div className="inline-field">
                <Field select="account" label="Choose account *">
                  <AccountChoiceWrapper accounts={accounts}>
                    <Select clearable={false} />
                  </AccountChoiceWrapper>
                </Field>
              </div>}
            <div className="inline-field">
              <Field select="country_code" label="Choose a country *">
                <CountryCodeSelect allowedCountryCodes={accountCountryCodes} />
              </Field>
            </div>
            <div className="inline-field">
              <Field select="type" label="Types of number *">
                <TypesOfNumber availableTypes={availableTypes} />
              </Field>
            </div>
            <div className="inline-field">
              <Field select="phrase" label="Refine by digits or phrases">
                <BlurInput type="text" placeholder="e.g. '01243'" />
              </Field>
            </div>
            {countryCodesWithRegions.indexOf(countryCode) !== -1 &&
              <div className="inline-field">
                <Field select="region" label="Location">
                  <BlurInput type="text" />
                </Field>
              </div>}
          </Fieldset>
        </Form>
      </div>
    );
  }
}

class TypesOfNumber extends React.Component {

  static propTypes = {
    availableTypes: PropTypes.array,
    value:          PropTypes.array,
    onChange:       PropTypes.func
  };

  render() {
    const { availableTypes, value = [], onChange } = this.props;

    if (!availableTypes.length) {
      return (
        <div className="number-types">
          -
        </div>
      );
    }

    return (
      <div className="number-types">
        {availableTypes.map((type, index) =>
          <Radio
            key={index}
            choice={type}
            value={value}
            label={numberTypes[type]}
            onChange={() => onChange(type)}
          />
        )}
      </div>
    );
  }
}

export default AvailableListForm;
