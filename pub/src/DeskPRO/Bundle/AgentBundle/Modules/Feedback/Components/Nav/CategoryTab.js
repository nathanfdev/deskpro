import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class CategoryTab extends Component {

  static propTypes = {
    customCategories: PropTypes.object.isRequired
  };

  render() {
    const { customCategories } = this.props;
    console.log(customCategories.toArray());

    return (
      <ul>
        {customCategories.toArray().map((item, index) => {
            console.log('Item', item);
           return ( <ListItemContainer key={index}
                               label={item.get('group')}
                               listOptions={{isComments: false, navItem: {custom_category: item.get('group')}}}>

              <ListItem count={item.get('count')}
                        label={item.get('group')} />
            </ListItemContainer> )
          }
        )}
      </ul>
    );
  }
}