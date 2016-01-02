import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

export class CategoryTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    categories: PropTypes.object.isRequired
  };

  render() {
    const { categories, loaded } = this.props;

    return (
      <LoadIndicator loaded={loaded}>
      <ul>
        {categories && categories.get('nested').map((item, index) =>
            <ListItemContainer key={index}
                               label={item.get('title')}
                               listOptions={{isComments: false, navItem: {custom_category: item.get('title')}}}>

              <ListItem count={item.get('count')}
                        label={item.get('title')}/>
            </ListItemContainer>
        )}
      </ul>
      </LoadIndicator>
    );
  }
}