import PropTypes from 'prop-types';
import React from 'react';
import countries from 'countries-list/countries.json';
import classNames from 'classnames';
import Select from './Select';

class CountryCodeSelect extends React.Component {

  static propTypes = {
    allowedCountryCodes: PropTypes.array
  };

  renderValue = option => (
    <span>
      <i className={classNames('flag-icon', `flag-icon-${option.value.toLowerCase()}`)} />
      {option.label}
    </span>
  );

  render() {
    const { allowedCountryCodes } = this.props;
    const countryCodes = allowedCountryCodes || Object.keys(countries.countries);
    const choices = countryCodes.map(countryCode => ({
      value: countryCode,
      label: countries.countries[countryCode].name
    }));

    return (
      <Select
        {...this.props}
        clearable={false}
        choices={choices}
        optionRenderer={this.renderValue}
        valueRenderer={this.renderValue}
      />
    );
  }
}

export default CountryCodeSelect;
