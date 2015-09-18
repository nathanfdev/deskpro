import React, { Component, PropTypes } from 'react';
import { ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class TypeTab extends React.Component {

  render() {
    const { types, onClick } = this.props;

    return (
      <ul>
        {types.map((item, index) =>
            <div key={index} onClick={onClick.bind(this, {'category':item.title})}>
              <ListItem count={item.value} label={item.title}/>
            </div>
        )}
      </ul>
    );
  }
}