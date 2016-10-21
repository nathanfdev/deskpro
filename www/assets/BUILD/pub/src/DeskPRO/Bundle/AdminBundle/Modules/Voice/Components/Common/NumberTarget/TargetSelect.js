import React from 'react';
import { Select } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';

const voiceTargetTypes = [
  { value: 'queue', label: 'Queue', icon: 'fa-list-ul' },
  { value: 'agent', label: 'Agent', icon: 'fa-bullseye' },
  { value: 'auto_attendant', label: 'Auto Attendant', icon: 'fa-sitemap' }
];

class TargetSelect extends React.Component {

  renderValue = option => (
    <span>
      <i className={classNames('fa', option.icon)} />
      {option.label}
    </span>
  );

  render() {
    return (
      <Select
        {...this.props}
        choices={voiceTargetTypes}
        optionRenderer={this.renderValue}
        valueRenderer={this.renderValue}
      />
    );
  }
}

export default TargetSelect;
