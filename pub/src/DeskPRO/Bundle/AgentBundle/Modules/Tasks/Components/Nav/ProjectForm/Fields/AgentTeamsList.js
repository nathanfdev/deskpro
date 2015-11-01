import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';

export class AgentTeamsList extends React.Component {

  static propTypes = {
    selected: PropTypes.array,
    values: PropTypes.object,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { values = [], selected, onChange } = this.props;
    const options = values.map(value => ({
      label: value.get('name'),
      value: value.get('id')
    }));

    return (
      <CheckboxList options={options}
                    selected={selected}
                    onChange={onChange} />
    );
  }
}
