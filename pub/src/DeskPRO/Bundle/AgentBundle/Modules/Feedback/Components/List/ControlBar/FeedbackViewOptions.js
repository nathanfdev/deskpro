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
import {storeDisplayFieldsToPersonSetting, getDisplayFieldsFromPersonSetting} from '../../../Actions/FeedbackListActions';
// import MenuFooterLink from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterLink';

const FeedbackViewOptions = React.createClass({
  propTypes: {
    dispatch: PropTypes.func.isRequired,
    toggleOptionsMenu: PropTypes.func.isRequired,
    feedbackViewFields: PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired
  },

  mixins: [require('react-onclickoutside')],

  getInitialState() {
    const {dispatch, feedbackViewFields} = this.props;
    const defaultCardState = {
      id: { isShown: true },
      hidden_status: { isShown: true },
      status_category: { isShown: true },
      custom_category: { isShown: true },
      date_created: { isShown: true },
      total_rating: { isShown: true },
      num_rating: { isShown: true },
      num_comments: { isShown: true },
      validating: { isShown: true }
    };
    console.log(feedbackViewFields);
    return {
      card: feedbackViewFields ? feedbackViewFields : defaultCardState
    };
  },

  componentWillUnmount: function componentWillUnmount() {
    const {dispatch} = this.props;
    dispatch(storeDisplayFieldsToPersonSetting(this.state));
  },

  handleClickOutside: function handleClickOutside() {
    this.props.toggleOptionsMenu();
  },

  changeState: function changeState(type, field, isChecked) {
    const currentState = this.state[type];
    currentState[field].isShown = isChecked;
    this.setState({ [type]: currentState });
  },

  render: function render() {
    const {currentViewMode} = this.props;
    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
        <Item discMarked
              label="Card view"
              widgetClass="dpw-navigation-dropdown-column-list-item"
              isActive={currentViewMode === constants.VIEW_MODE_CARD}
          >
          <ItemList>
            <CardViewFieldsList
              currentViewMode={currentViewMode}
              ref="cardViewFields"
              changeState={this.changeState.bind(this, constants.VIEW_MODE_CARD)}
              fields={this.state.card}
              />
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
