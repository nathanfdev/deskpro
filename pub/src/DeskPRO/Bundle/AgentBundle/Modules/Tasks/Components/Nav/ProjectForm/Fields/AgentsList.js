import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';

export class AgentsList extends React.Component {

  static propTypes = {
    values: PropTypes.object
  };

  static renderLabel(value) {
    return (
      <span>
          <span style={{position: 'relative'}}>
            <PersonAvatar person={value} size="16" />
          </span>
          {value.get('name')}
      </span>
    );
  }

  render() {
    const { values = [] } = this.props;
    const options = values.map(value => ({
      label: AgentsList.renderLabel(value),
      value: value.get('id')
    }));

    return (
      <CheckboxList {...this.props} options={options} />
    );
  }
}
