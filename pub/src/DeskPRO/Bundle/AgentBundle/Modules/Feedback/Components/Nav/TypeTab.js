import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class TypeTab extends Component {

  static propTypes = {
    types: PropTypes.array.isRequired,
    currentGroup: PropTypes.object.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { types, onClick, currentGroup } = this.props;

    return (
      <ul>
        {types.map((item, index) =>
          <div key={index} onClick={onClick.bind(this, {name: 'category', value: item.title})}>
            <ListItem count={item.value} label={item.title}
                      active={currentGroup.name === 'category' && currentGroup.value === item.title}
              />
          </div>
        )}
      </ul>
    );
  }
}
