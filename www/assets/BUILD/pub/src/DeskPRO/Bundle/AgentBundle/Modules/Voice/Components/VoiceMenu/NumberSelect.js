import React, { PropTypes } from 'react';
import { Select } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';

class NumberSelect extends React.Component {

  static propTypes = {
    numbers: PropTypes.object
  };

  renderValue = option => (
    <span>
      <i className={classNames('flag-icon', `flag-icon-${option.country_code.toLowerCase()}`)} />
      {option.label}
      <span className="target">(Sales)</span>
    </span>
  );

  render() {
    const { numbers } = this.props;
    const choices = [];
    numbers.forEach((number) => {
      choices.push({
        label:        number.get('number'),
        value:        number.get('number'),
        country_code: number.get('country_code')
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
