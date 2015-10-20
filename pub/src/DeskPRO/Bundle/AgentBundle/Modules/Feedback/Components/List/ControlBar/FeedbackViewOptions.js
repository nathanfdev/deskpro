import React, {PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';
import {TableViewFieldsList} from './TableViewFieldsList';
import {CardViewFieldsList} from './CardViewFieldsList';
import ItemGroup from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemGroup';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
// import MenuFooterLink from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterLink';

const FeedbackViewOptions = React.createClass({
  propTypes: {
    toggleOptionsMenu: PropTypes.func.isRequired,
    currentViewMode: PropTypes.string.isRequired
  },

  mixins: [require('react-onclickoutside')],

  getInitialState() {
    return {};
  },

  onClose: function onClose() {
    console.log('onClose');
  },

  handleClickOutside: function handleClickOutside() {
    this.props.toggleOptionsMenu();
    this.onClose();
  },

  componentWillUnmount: function() {
    console.log('Unmounted');
  },

  changeState: function(type, field, checked) {
    if (this.state && this.state.hasOwnProperty(type)) {
      let existedProp = false;
      const exists = this.state[type].map((obj)=> {
        if (obj.field === field) {
          obj.shown = checked;
          existedProp = true;
        }
        return obj;
      });
      if (!existedProp) {
        exists.push({field: field, shown: checked});
      }
      this.setState({[type]: exists});
    } else {
      this.setState({[type]: [{field: field, shown: checked}]});
    }
  },

  render: function render() {
    const {currentViewMode} = this.props;
    console.log('Now state is', this.state);
    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
        <Item discMarked
              label="Card view"
              widgetClass="dpw-navigation-dropdown-column-list-item"
              isActive={currentViewMode === constants.VIEW_MODE_CARD}
          >
          <ItemList>
            <CardViewFieldsList currentViewMode={currentViewMode} ref="cardViewFields"
                                changeState={this.changeState.bind(this, constants.VIEW_MODE_CARD)}/>
          </ItemList>
        </Item>
        <Item discMarked
              label="Table view"
              widgetClass="dpw-navigation-dropdown-column-list-item"
              isActive={currentViewMode === constants.VIEW_MODE_TABLE}
          >
          <ItemList>
            <TableViewFieldsList currentViewMode={currentViewMode}/>
          </ItemList>
        </Item>
        <Item keepOpen label="Hello!">
          <Menu>
            <ItemGroup>
              <Item label="Test"/>
              <Item checked label="Test 2 (checked)"/>
            </ItemGroup>
            <Item icon="book" itemType="danger" checked label="Test 3 (danger, checked)">
              <Menu>
                <Item label="Sub-menu"/>
                <Item label="Submarine"/>
              </Menu>
            </Item>
            <Item icon="bolt" itemType="locked" label="Test 4 (locked)">
              <Menu>
                <Item label="Sub-menu 2"/>
                <Item label="Subterranean"/>
              </Menu>
            </Item>
            <Item itemType="danger" keepOpen label="Test 5 (danger)"/>
            <Item condensed label="Test 6 (condensed)"/>
            <Item condensed icon="book" itemType="danger" keepOpen label="Test 7 (danger, condensed)"/>
            <Item disabled label="Test 8 (disabled)"/>
            <MenuFooter>
              <MenuFooterOptions options={[{id: 'asc', onClick: () => {}, label: 'Asc'},
                                                 {id: 'desc', onClick: () => {}, label: 'Desc'}
                                                ]} active="asc">
                Sort
              </MenuFooterOptions>
            </MenuFooter>
          </Menu>
          <ItemList>
            <Item label="Test A"/>
            <Item label="Test B"/>
          </ItemList>
        </Item>
      </Menu>
    );
  }
});

module.exports = FeedbackViewOptions;
