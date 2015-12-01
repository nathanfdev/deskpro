import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class CategoryTab extends Component {

  static propTypes = {
    customCategories: PropTypes.object.isRequired
  };

  render() {
    const { customCategories } = this.props;

    return (
      <ul>
        {customCategories.toArray().map((item, index) =>
            <ListItemContainer key={index}
                               label={item.get('title')}
                               listOptions={{isComments: false, navItem: {custom_category: item.get('title')}}}>

              <ListItem count={item.get('count')}
                        label={item.get('title')}/>
            </ListItemContainer>
        )}
      </ul>
    );
  }
}