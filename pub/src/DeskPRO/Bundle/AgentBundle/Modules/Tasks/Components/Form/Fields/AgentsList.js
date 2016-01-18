import React, { PropTypes } from 'react';
import { BaseList } from './BaseList';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';

export class AgentsList extends BaseList {

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

}
