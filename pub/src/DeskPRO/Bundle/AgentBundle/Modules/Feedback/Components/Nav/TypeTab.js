import React, { Component, PropTypes } from 'react';
import { ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class TypeTab extends React.Component {

  render() {
    const { types, onClick } = this.props;
    let itemKey = 0;

    return (
      <ul>
        {types.map(item =>
            <div key={itemKey++} onClick={onClick.bind(this, {'category':item.title})}>
              <ListItem key={itemKey++} count={item.value} label={item.title}/>
            </div>
        )}
      </ul>
    );
  }
}