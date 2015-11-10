import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class TypeTab extends Component {
  static propTypes = {
    types: PropTypes.object.isRequired
  };

  render() {
    const { types } = this.props;
    console.log('Types', types.get('nested').toJS());
    return (
      <ul>
        {types.get('nested').toJS().map((item, index) =>
            <ListItemContainer key={index}
                               label={item.group}
                               listOptions={{navItem: {category: item.group}}}>

              <ListItem count={item.count}
                        label={item.group} />
            </ListItemContainer>
        )}
      </ul>
    );
  }
}
