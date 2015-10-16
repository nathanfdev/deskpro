import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';
import {ViewField} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ViewField';
import ItemGroup from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemGroup';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import MenuFooterLink from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterLink';

export class FeedbackViewOptions extends Component {

  static propTypes = {
    currentViewMode: PropTypes.string.isRequired
  };

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
            <CardViewFieldsList currentViewMode={currentViewMode}/>
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
}

export class CardViewFieldsList extends Component {
  render() {
    return (
      <div>
        <ViewField value="status" label="Status" status={constants.FIELD_REQUIRED} fixed locked/>
        <ViewField value="title" label="Title" status={constants.FIELD_REQUIRED} fixed/>
        <ViewField value="type" label="Type" status={constants.FIELD_REQUIRED} fixed/>
        <ViewField value="content" label="Content" status={constants.FIELD_REQUIRED} fixed/>
        <ViewField value="author_name" label="Submitter" status={constants.FIELD_REQUIRED} fixed/>
        <li>
          <hr/>
        </li>
        <ViewField value="id" label="ID" status={constants.FIELD_REQUIRED}/>
        <ViewField value="hidden_status" label="Hidden status" status={constants.FIELD_REQUIRED}/>
        <ViewField value="status_category" label="Status category" status={constants.FIELD_REQUIRED}/>
        <ViewField value="custom_category" label="Category" status={constants.FIELD_REQUIRED}/>
        <ViewField value="language_id" label="Lang" status={constants.FIELD_REQUIRED}/>
        <ViewField value="slug" label="Slug" status={constants.FIELD_REQUIRED}/>
        <ViewField value="date_created" label="Created" status={constants.FIELD_REQUIRED}/>
        <ViewField value="date_published" label="Published" status={constants.FIELD_REQUIRED}/>
        <ViewField value="view_count" label="Views" status={constants.FIELD_REQUIRED}/>
        <ViewField value="total_rating" label="Rating" status={constants.FIELD_REQUIRED}/>
        <ViewField value="num_rating" label="Votes" status={constants.FIELD_REQUIRED}/>
        <ViewField value="num_comments" label="Comments" status={constants.FIELD_REQUIRED}/>
        <ViewField value="validating" label="Validating" status={constants.FIELD_REQUIRED}/>
        <ViewField value="popularity" label="Popularity" status={constants.FIELD_REQUIRED}/>
      </div>
    );
  }
}

export class TableViewFieldsList extends Component {
  render() {
    return (
      <div>
        <ViewField value="id" label="ID" status={constants.FIELD_REQUIRED}/>
        <ViewField value="status" label="Status" status={constants.FIELD_REQUIRED}/>
        <ViewField value="hidden_status" label="Hidden status" status={constants.FIELD_REQUIRED}/>
        <ViewField value="title" label="Title" status={constants.FIELD_REQUIRED}/>
        <ViewField value="content" label="Content" status={constants.FIELD_REQUIRED}/>
        <ViewField value="status_category" label="Status category" status={constants.FIELD_REQUIRED}/>
        <ViewField value="custom_category" label="Category" status={constants.FIELD_REQUIRED}/>
        <ViewField value="author_name" label="Submitter" status={constants.FIELD_REQUIRED}/>
        <li>
          <hr/>
        </li>
        <ViewField value="language_id" label="Lang" status={constants.FIELD_REQUIRED}/>
        <ViewField value="type" label="Type" status={constants.FIELD_REQUIRED}/>
        <ViewField value="slug" label="Slug" status={constants.FIELD_REQUIRED}/>
        <ViewField value="date_created" label="Created" status={constants.FIELD_REQUIRED}/>
        <ViewField value="date_published" label="Published" status={constants.FIELD_REQUIRED}/>
        <ViewField value="view_count" label="Views" status={constants.FIELD_REQUIRED}/>
        <ViewField value="total_rating" label="Rating" status={constants.FIELD_REQUIRED}/>
        <ViewField value="num_rating" label="Votes" status={constants.FIELD_REQUIRED}/>
        <ViewField value="num_comments" label="Comments" status={constants.FIELD_REQUIRED}/>
        <ViewField value="validating" label="Validating" status={constants.FIELD_REQUIRED}/>
        <ViewField value="popularity" label="Popularity" status={constants.FIELD_REQUIRED}/>
      </div>
    );
  }
}

export class MenuTestStuff extends Component {
  render() {
    return (
      <div/>
    );
  }
}

