import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, BlurInput, Select, CountryCodeSelect, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
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

const twilioCountryCodes = {
  AE: ['tollfree'],
  AR: ['local', 'tollfree'],
  AT: ['local', 'national', 'tollfree'],
  AU: ['local', 'mobile', 'tollfree'],
  BA: ['local', 'national'],
  BB: ['local'],
  BE: ['local', 'national', 'tollfree'],
  BG: ['local', 'tollfree'],
  BJ: ['mobile'],
  BO: ['tollfree'],
  BR: ['local', 'mobile', 'tollfree'],
  BW: ['tollfree'],
  BY: ['tollfree'],
  CA: ['local', 'tollfree'],
  CH: ['local', 'tollfree'],
  CL: ['local'],
  CO: ['local', 'tollfree'],
  CY: ['local', 'national'],
  CZ: ['local', 'national', 'tollfree'],
  DE: ['local', 'national', 'mobile'],
  DK: ['local', 'mobile', 'tollfree'],
  DO: ['local'],
  DZ: ['local', 'national'],
  EC: ['local'],
  EE: ['local', 'national'],
  ES: ['local', 'national', 'tollfree'],
  FI: ['local', 'national', 'tollfree'],
  FR: ['local', 'national', 'mobile'],
  GB: ['local', 'national', 'mobile', 'tollfree'],
  GD: ['local'],
  GH: ['mobile'],
  GN: ['mobile'],
  GR: ['local', 'tollfree'],
  GT: ['local'],
  HK: ['local', 'national', 'tollfree'],
  HR: ['local'],
  HU: ['local'],
  ID: ['local', 'tollfree'],
  IE: ['local', 'national'],
  IL: ['local', 'national', 'mobile', 'tollfree'],
  IN: ['tollfree'],
  IS: ['local'],
  IT: ['local', 'national'],
  JM: ['local'],
  JP: ['local', 'national', 'tollfree'],
  KE: ['local'],
  KR: ['tollfree'],
  KY: ['local'],
  LT: ['local'],
  LU: ['local'],
  LV: ['local'],
  ML: ['local'],
  MO: ['mobile'],
  MT: ['local', 'national'],
  MU: ['mobile'],
  MX: ['local', 'tollfree'],
  MY: ['mobile', 'tollfree'],
  NA: ['local', 'national'],
  NG: ['local'],
  NI: ['local'],
  NL: ['local', 'national', 'tollfree'],
  NO: ['local', 'tollfree'],
  NZ: ['local', 'tollfree'],
  PA: ['local', 'tollfree'],
  PE: ['local', 'tollfree'],
  PH: ['local', 'tollfree'],
  PL: ['local', 'tollfree'],
  PR: ['local'],
  PT: ['local', 'national', 'tollfree'],
  RO: ['local', 'tollfree'],
  RS: ['tollfree'],
  SD: ['local'],
  SE: ['local', 'national', 'tollfree'],
  SI: ['local'],
  SK: ['local', 'tollfree'],
  SV: ['local'],
  TH: ['local', 'tollfree'],
  TN: ['local', 'national'],
  TT: ['local'],
  TW: ['local', 'tollfree'],
  TZ: ['local', 'national'],
  UG: ['local', 'national', 'tollfree'],
  US: ['local', 'tollfree'],
  VE: ['tollfree'],
  VN: ['local', 'tollfree'],
  ZA: ['local', 'national', 'tollfree']
};

const countryCodesWithRegions = ['US', 'CA', 'TW'];
const numberTypes = {
  local:    'Local',
  tollfree: 'Toll free',
  mobile:   'Mobile',
  national: 'National',
  fixed:    'Fixed'
};
const defaultNumberTypes = {
  twilio: ['local', 'tollfree', 'mobile', 'national'],
  plivo:  ['local', 'tollfree', 'mobile', 'national', 'fixed']
};

class AvailableListForm extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    accounts: PropTypes.object,
    onChange: PropTypes.func
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
      const { accounts } = this.props;
      const account = accounts.get(formData.value.account);

      formData.value.types = [];
      if (account) {
        formData.value.types = defaultNumberTypes[account.get('type')];
        if (account.get('type') === 'twilio' && formData.value.country_code && twilioCountryCodes[formData.value.country_code]) {
          formData.value.types = [...twilioCountryCodes[formData.value.country_code]];
        }
      }
    }

    this.setState({ formData });
    this.props.onChange(formData.value);
  };

  render() {
    const { accounts = Immutable.fromJS([]) } = this.props;
    const { formData } = this.state;
    const account = accounts.get(formData.value.account);
    const countryCode = formData.value.country_code;

    let accountCountryCodes = defaultCountryCodes;
    if (account && account.get('type') === 'twilio') {
      accountCountryCodes = Object.keys(twilioCountryCodes);
    }

    let availableTypes = [];
    if (account) {
      availableTypes = [...defaultNumberTypes[account.get('type')]];
      if (account.get('type') === 'twilio') {
        if (countryCode && twilioCountryCodes[countryCode]) {
          availableTypes = [...twilioCountryCodes[countryCode]];
        }

        // no numbers with national type, so just don't display this option at all
        const nationalIndex = availableTypes.indexOf('national');
        if (nationalIndex !== -1) {
          availableTypes.splice(nationalIndex, 1);
        }
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
              <Field select="types" label="Types of number *">
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

  onChange = (item) => {
    const { value = [], onChange } = this.props;
    const index = value.indexOf(item);

    if (index !== -1) {
      value.splice(index, 1);
    } else {
      value.push(item);
    }

    onChange(value);
  };

  render() {
    const { availableTypes, value = [] } = this.props;

    return (
      <div className="number-types">
        {availableTypes.map((type, index) => {
          const checked = value.indexOf(type) !== -1;

          return (
            <Checkbox
              key={index}
              value={checked}
              label={numberTypes[type]}
              onChange={() => this.onChange(type)}
            />
          );
        })}
      </div>
    );
  }
}

export default AvailableListForm;
