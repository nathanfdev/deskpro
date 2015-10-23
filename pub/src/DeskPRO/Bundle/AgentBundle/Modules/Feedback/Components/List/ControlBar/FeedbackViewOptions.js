import React, { PropTypes } from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';
import { TableViewFieldsList } from './TableViewFieldsList';
import { CardViewFieldsList } from './CardViewFieldsList';
import ItemGroup from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemGroup';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import { storeDisplayFieldsToPersonSetting, updateDisplayFieldsToPersonSetting, getDisplayFieldsFromPersonSetting } from '../../../Actions/FeedbackListActions';
// import MenuFooterLink from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterLink';


const defaultCardFields = {
  id: { isShown: true },
  custom_category: { isShown: true },
  date_created: { isShown: true }
};

const defaultTableFields = {
  id: { isShown: true },
  num_ratings: { isShown: true },
  title: { isShown: true },
  content: { isShown: true },
  hidden_status: { isShown: true },
  status_category: { isShown: true },
  type: { isShown: true },
  custom_category: { isShown: true },
  labels: { isShown: true },
  author_name: { isShown: true },
  num_comments: { isShown: true },
  date_created: { isShown: true },
  total_rating: { isShown: true }
};

const FeedbackViewOptions = React.createClass({
  propTypes: {
    dispatch: PropTypes.func.isRequired,
    toggleOptionsMenu: PropTypes.func.isRequired,
    viewFields: PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired
  },

  mixins: [require('react-onclickoutside')],

  getInitialState() {
    const { viewFields } = this.props;

    return {
      isStored: false,
      isChanged: false,
      card: (viewFields && viewFields.card) ? viewFields.card : defaultCardFields,
      table: (viewFields && viewFields.table) ? viewFields.table : defaultTableFields
    };
  },

  componentWillUnmount() {
    const {dispatch, viewFields} = this.props;
    if (this.state.isChanged) {
      if (this.state.isStored || viewFields) {
        dispatch(updateDisplayFieldsToPersonSetting(this.state));
      } else {
        dispatch(storeDisplayFieldsToPersonSetting(this.state));
      }
      this.setState({ isStored: true });
    }
  },

  handleClickOutside() {
    this.props.toggleOptionsMenu();
  },

  changeState(type, field, isChecked) {
    const currentState = this.state[type];
    currentState[field].isShown = isChecked;
    this.setState({ [type]: currentState });
    this.setState({ isChanged: true });
  },

  render() {
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
            <TableViewFieldsList
              currentViewMode={currentViewMode}
              changeState={this.changeState.bind(this, constants.VIEW_MODE_TABLE)}
              fields={this.state.table}
              />
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

module.exports.FeedbackViewOptions = FeedbackViewOptions;
module.exports.defaultCardFields = defaultCardFields;
module.exports.defaultTableFields = defaultTableFields;
