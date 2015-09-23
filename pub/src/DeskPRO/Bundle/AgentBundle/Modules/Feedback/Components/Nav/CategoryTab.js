import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class CategoryTab extends React.Component {

  static propTypes = {
    currentGroup: PropTypes.object.isRequired,
    customCategories: PropTypes.array.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { customCategories, onClick, currentGroup } = this.props;

    return (
      <ul>
        {customCategories.map((item, index) =>
            <div key={index}
                 onClick={onClick.bind(this, {name:'custom_category', value:item.group})}>
              <ListItem count={item.count} label={item.group}
                        active={currentGroup.name === 'custom_category' && currentGroup.value === item.title}
                />
            </div>
        )}
      </ul>
    );
  }
}