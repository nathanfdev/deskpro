import React from 'react';
import { BaseList } from './BaseList';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';

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

  getKeyword(value) {
    return value.get('name');
  }
}
