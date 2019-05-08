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
  AT: ['national', 'tollfree'],
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
  CY: ['national'],
  CZ: ['local', 'national', 'tollfree'],
  DE: ['local', 'mobile', 'national'],
  DK: ['local', 'mobile', 'tollfree'],
  DO: ['local'],
  DZ: ['local', 'national'],
  EC: ['local'],
  EE: ['local', 'national'],
  ES: ['local', 'national', 'tollfree'],
  FI: ['local', 'national', 'tollfree'],
  FR: ['local', 'mobile', 'national'],
  GB: ['local', 'mobile', 'national', 'tollfree'],
  GD: ['local'],
  GH: ['mobile'],
  GN: ['mobile'],
  GR: ['local', 'tollfree'],
  GT: ['local'],
  HK: ['national', 'tollfree'],
  HR: ['local'],
  HU: ['local'],
  ID: ['local', 'tollfree'],
  IE: ['local', 'national'],
  IL: ['local', 'mobile', 'national', 'tollfree'],
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
  MT: ['national'],
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
  PT: ['national', 'tollfree'],
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
  twilio: [
    {
      value: 'local',
      title: 'Local'
    },
    {
      value: 'tollfree',
      title: 'Toll free'
    },
    {
      value: 'mobile',
      title: 'Mobile'
    },
    {
      value: 'national',
      title: 'National'
    }
  ],
  plivo: [
    {
      value: 'local',
      title: 'Local'
    },
    {
      value: 'tollfree',
      title: 'Toll free'
    },
    {
      value: 'mobile',
      title: 'Mobile'
    },
    {
      value: 'national',
      title: 'National'
    },
    {
      value: 'fixed',
      title: 'Fixed'
    }
  ]
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

  onChange = (formData) => {
    this.setState({ formData });
    this.props.onChange(formData.value);
  };

  render() {
    const { accounts = Immutable.fromJS([]) } = this.props;
    const { formData } = this.state;
    const account = accounts.get(formData.value.account);

    let accountCountryCodes = defaultCountryCodes;
    if (account && account.get('type') === 'twilio') {
      accountCountryCodes = Object.keys(twilioCountryCodes);
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
                <CountryCodeSelect allowedCountryCodes={accountCountryCodes} account={account} />
              </Field>
            </div>
            <div className="inline-field">
              <Field select="types" label="Types of number *">
                <TypesOfNumber account={account} />
              </Field>
            </div>
            <div className="inline-field">
              <Field select="phrase" label="Refine by digits or phrases">
                <BlurInput type="text" placeholder="e.g. '01243'" />
              </Field>
            </div>
            {countryCodesWithRegions.indexOf(formData.value.country_code) !== -1 &&
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
    account:  PropTypes.object,
    value:    PropTypes.array,
    onChange: PropTypes.func
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
    const { account, value = [] } = this.props;
    let types = [];
    if (account) {
      types = numberTypes[account.get('type')];
    }

    return (
      <div className="number-types">
        {types.map((type, index) => {
          const checked = value.indexOf(type.value) !== -1;

          return (
            <Checkbox
              key={index}
              value={checked}
              label={type.title}
              onChange={() => this.onChange(type.value)}
            />
          );
        })}
      </div>
    );
  }
}

export default AvailableListForm;
