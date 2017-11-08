import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { Select, Input, Datepicker } from '@deskpro/react-components';

export class FollowUpTime extends React.Component {
  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func,
  };

  static defaultProps = {
    value: {},
    onChange() {},
  };

  static presets = [
    { value: '15 minutes', label: '15 minutes' },
    { value: '1 hours', label: '1 hour' },
    { value: '6 hours', label: '6 hours' },
    { value: '1 days', label: '1 day' },
    { value: '3 days', label: '3 days' },
  ];

  onSelectorUnitChange = (unit) => {
    let input = '';
    if (this.props.value.type === 'selector') {
      [input] = this.props.value.value.split(' ');
    }
    this.props.onChange({
      type:  'selector',
      value: `${input} ${unit.value}`
    });
  };

  onSelectorValueChange = (value) => {
    let unit = null;
    let input; // eslint-disable-line no-unused-vars
    if (this.props.value.type === 'selector') {
      [input, unit] = this.props.value.value.split(' ');
    }
    this.props.onChange({
      type:  'selector',
      value: `${value} ${unit}`
    });
  };

  selectPreset = (preset) => {
    this.props.onChange({
      type:  'preset',
      value: preset.value
    });
  };

  renderPresets = () => FollowUpTime.presets.map((preset) => {
    const selected = this.props.value.type === 'preset' && this.props.value.value === preset.value;
    return (<li
      key={preset.value}
      onClick={() => this.selectPreset(preset)}
      className={classNames({ selected })}
    >
      {preset.label}
    </li>);
  });

  renderSelector = () => {
    const options = [
      { value: 'minutes', label: 'Minutes' },
      { value: 'hours', label: 'Hours' },
      { value: 'days', label: 'Days' },
      { value: 'months', label: 'Months' },
    ];
    let input = '';
    let unit = null;
    if (this.props.value.type === 'selector') {
      [input, unit] = this.props.value.value.split(' ');
    }
    return (
      <li>
        <Input
          value={input}
          className="selector_value"
          onChange={this.onSelectorValueChange}
        />
        <Select
          clearable={false}
          searchable={false}
          value={unit}
          onChange={this.onSelectorUnitChange}
          options={options}
        />
      </li>
    );
  };

  renderPicker = () => (
    <li>
      <Datepicker />
    </li>
    );

  render() {
    return (
      <div className="time">
        <ul>
          {this.renderPresets()}
          {this.renderSelector()}
          {this.renderPicker()}
        </ul>
      </div>
    );
  }
}
