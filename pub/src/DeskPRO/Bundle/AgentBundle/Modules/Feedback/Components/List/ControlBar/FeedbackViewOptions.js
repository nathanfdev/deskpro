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

  static propTypes = {};

  render() {
    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
        <CardViewFieldsList/>
        <TableViewFieldsList/>
        <MenuTestStuff/>
      </Menu>
    );
  }
}

export class TableViewFieldsList extends Component {
  render() {
    return (
      <Item widgetClass="dpw-navigation-dropdown-column-list-item" discMarked>
        Table view
        <ItemList>
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
        </ItemList>
      </Item>
    );
  }
}

export class CardViewFieldsList extends Component {
  render() {
    return (
      <Item widgetClass="dpw-navigation-dropdown-column-list-item" isActive discMarked>
        Card view
        <ItemList>
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
          <ViewField value="custom_category" label="Category" status={constants.FIELD_REQUIRED} />
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
        </ItemList>
      </Item>
    );
  }
}

export class MenuTestStuff extends Component {
  render() {
    return (
      <Item keepOpen>
        Hello!
        <Menu>
          <ItemGroup>
            <Item>
              Test
            </Item>
            <Item checked>
              Test 2
            </Item>
          </ItemGroup>
          <Item icon="book" itemType="danger" checked>
            Test 3
            <Menu>
              <Item>Sub-menu</Item>
              <Item>Submarine</Item>
            </Menu>
          </Item>
          <Item icon="bolt" itemType="locked">
            Test 4
            <Menu>
              <Item>Sub-menu 2</Item>
              <Item>Subterranean</Item>
            </Menu>
          </Item>
          <Item itemType="danger" keepOpen>
            Test 5
          </Item>
          <Item condensed>
            Test 6
          </Item>
          <Item condensed icon="book" itemType="danger" keepOpen>
            Test 7
          </Item>
          <Item disabled>
            Test 8
          </Item>
          <MenuFooter>
            <MenuFooterOptions options={[{id: 'asc', onClick: () => {}, label: 'Asc'},
                                                 {id: 'desc', onClick: () => {}, label: 'Desc'}
                                                ]} active="asc">
              Sort
            </MenuFooterOptions>
          </MenuFooter>
        </Menu>
        <ItemList>
          <Item>Test A</Item>
          <Item>Test B</Item>
        </ItemList>
      </Item>
    );
  }
}

