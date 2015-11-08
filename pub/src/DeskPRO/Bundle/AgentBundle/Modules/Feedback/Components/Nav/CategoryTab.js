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
        {customCategories.toJS().map((item, index) =>
            <ListItemContainer key={index}
                               label={item.group}
                               listOptions={{navItem: {custom_category: item.group}}}>

              <ListItem count={item.count}
                        label={item.group} />
            </ListItemContainer>
        )}
      </ul>
    );
  }
}