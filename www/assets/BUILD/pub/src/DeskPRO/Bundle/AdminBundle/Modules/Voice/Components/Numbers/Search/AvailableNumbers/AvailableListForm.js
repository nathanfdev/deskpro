import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, BlurInput, Select, CountryCodeSelect, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import AccountChoiceWrapper from '../../../Common/AccountChoiceWrapper';

const allowedCountryCodes = [
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
                <CountryCodeSelect allowedCountryCodes={allowedCountryCodes} />
              </Field>
            </div>
            <div className="inline-field">
              <Field select="types" label="Types of number *">
                <TypesOfNumber account={accounts.get(formData.value.account)} />
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
