import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { Select, Input, Datetimepicker } from '@deskpro/react-components';

class FollowUpTime extends React.Component {
  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func,
  };

  static defaultProps = {
    value: {},
    onChange() {},
  };

  static presets = [
    { time: { value: '15', unit: 'minutes' }, label: '15 minutes' },
    { time: { value: '1', unit: 'hours' }, label: '1 hour' },
    { time: { value: '6', unit: 'hours' }, label: '6 hours' },
    { time: { value: '1', unit: 'days' }, label: '1 day' },
    { time: { value: '3', unit: 'days' }, label: '3 days' },
  ];

  constructor(props) {
    super(props);

    this.state = {
      pickerValue: ''
    };
  }

  onSelectorUnitChange = (unit) => {
    let value = '';
    if (this.props.value.type === 'selector') {
      value = this.props.value.time.value;
    }
    this.setState({
      pickerValue: ''
    });
    this.props.onChange({
      type: 'selector',
      time: { unit: unit.value, value },
    });
  };

  onSelectorValueChange = (value) => {
    let unit = null;
    if (value.match(/^\d*$/)) {
      if (this.props.value.type === 'selector') {
        unit = this.props.value.time.unit;
      }
      this.setState({
        pickerValue: ''
      });
      this.props.onChange({
        type: 'selector',
        time: { value, unit },
      });
    }
  };

  onPickerSelect = (date) => {
    this.props.onChange({
      type: 'picker',
      time: { date }
    });
  };

  onPickerChange = (value) => {
    this.setState({
      pickerValue: value
    });
  };

  selectPreset = (preset) => {
    this.setState({
      pickerValue: ''
    });
    this.props.onChange({
      type: 'preset',
      time: preset.time
    });
  };

  renderPresets = () => FollowUpTime.presets.map((preset) => {
    const selected = this.props.value.type === 'preset' && this.props.value.time === preset.time;
    return (<li
      key={`${preset.time.value} ${preset.time.unit}`}
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
    let value = '';
    let unit  = null;
    if (this.props.value.type === 'selector') {
      ({ value, unit } = this.props.value.time);
    }
    return (
      <li>
        <Input
          value={value}
          className="selector_value"
          onChange={this.onSelectorValueChange}
        />
        <Select
          clearable={false}
          searchable={false}
          className="unit"
          value={unit}
          onChange={this.onSelectorUnitChange}
          options={options}
        />
      </li>
    );
  };

  renderPicker = () => (
    <li>
      <Datetimepicker
        locale={window.DP_LOCALE}
        onSelect={this.onPickerSelect}
        onChange={this.onPickerChange}
        value={this.state.pickerValue}
      />
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
export default FollowUpTime;
