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
    allowedCountryCodes: PropTypes.array,
    primaryCountryCodes: PropTypes.array
  };

  static defaultProps = {
    multiple:            false,
    primaryCountryCodes: []
  };

  renderValue = option => (
    <span>
      <i className={classNames('flag-icon', `flag-icon-${option.value.toLowerCase()}`)} />
      {option.label}
    </span>
  );

  render() {
    const { allowedCountryCodes, primaryCountryCodes, multiple, value } = this.props;

    delete countries.countries.XK;

    const countryCodes = allowedCountryCodes || Object.keys(countries.countries);
    const choices = Object.values(countryCodes.map(countryCode => ({
      value:   countryCode,
      label:   countries.countries[countryCode] ? countries.countries[countryCode].name : '',
      primary: primaryCountryCodes.indexOf(countryCode) !== -1
    }))).sort((a, b) => {
      if (a.primary) {
        return -1;
      }
      if (b.primary) {
        return 1;
      }

      return a.label.localeCompare(b.label);
    });

    let selectValue;
    if (Array.isArray(value)) {
      selectValue = value.map(item => item.toUpperCase());
    } else if (value) {
      selectValue = value.toUpperCase();
    }

    if (multiple) {
      const multiChoices = Object.values(choices.map(choice => ({ ...choice, label: this.renderValue(choice) })));

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
