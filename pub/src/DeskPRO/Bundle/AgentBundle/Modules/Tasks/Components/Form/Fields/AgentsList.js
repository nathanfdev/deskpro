import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';

export class AgentsList extends React.Component {

  static propTypes = {
    values: PropTypes.object.isRequired
  };

  renderLabel(value) {
    return (
      <span className="name">
          <span style={{position: 'relative'}}>
            <PersonAvatar person={value} size={16} />
          </span>
          {value.get('name')}
      </span>
    );
  }

  render() {
    return (
      <CheckboxList {...this.props} renderLabel={this.renderLabel} />
    );
  }
}
