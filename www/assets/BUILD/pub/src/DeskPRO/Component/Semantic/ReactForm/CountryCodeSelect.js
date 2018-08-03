import PropTypes from 'prop-types';
import React from 'react';
import countries from 'countries-list/countries.json';
import classNames from 'classnames';
import Select from './Select';
import MultiSelect from './MultiSelect';

class CountryCodeSelect extends React.Component {

  static propTypes = {
    value:               PropTypes.any, // eslint-disable-line react/forbid-prop-types
    multiple:            PropTypes.bool,
    allowedCountryCodes: PropTypes.array
  };

  static defaultProps = {
    multiple: false
  };

  renderValue = option => (
    <span>
      <i className={classNames('flag-icon', `flag-icon-${option.value.toLowerCase()}`)} />
      {option.label}
    </span>
  );

  render() {
    const { allowedCountryCodes, multiple, value } = this.props;
    const countryCodes = allowedCountryCodes || Object.keys(countries.countries);

    delete countries.countries.XK;

    const choices = countryCodes.map(countryCode => ({
      value: countryCode,
      label: countries.countries[countryCode].name
    }));

    let selectValue;
    if (Array.isArray(value)) {
      selectValue = value.map(item => item.toUpperCase());
    } else if (value) {
      selectValue = value.toUpperCase();
    }

    if (multiple) {
      const multiChoices = Object.values(choices.map(choice => ({
        value: choice.value,
        label: this.renderValue(choice)
      })));

      return (
        <MultiSelect
          {...this.props}
          value={selectValue}
          choices={multiChoices}
        />
      );
    }

    return (
      <Select
        {...this.props}
        value={selectValue}
        clearable={false}
        choices={choices}
        optionRenderer={this.renderValue}
        valueRenderer={this.renderValue}
      />
    );
  }
}

export default CountryCodeSelect;
