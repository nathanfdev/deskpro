import PropTypes from 'prop-types';
import React from 'react';
import { Select } from 'DeskPRO/Component/Semantic/ReactForm';
import { getPhoneCountryCode } from 'DeskPRO/Component/Util/PhoneNumber';
import classNames from 'classnames';

class NumberSelect extends React.Component {

  static propTypes = {
    numbers: PropTypes.object
  };

  renderValue = option => (
    <span>
      <i className={classNames('flag-icon', `flag-icon-${option.country_code.toLowerCase()}`)} />
      {option.label}
      {option.nickname && <span className="target">({option.nickname})</span>}
    </span>
  );

  render() {
    const { numbers } = this.props;
    const choices = [];
    numbers.forEach((number) => {
      choices.push({
        label:        number.get('number'),
        value:        number.get('id'),
        country_code: getPhoneCountryCode(number.get('number')),
        nickname:     number.get('nickname')
      });
    });

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

export default NumberSelect;
