import React, {Component, PropTypes} from 'react';
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
          <ViewField label="Status"/>
          <ViewField label="Submitter"/>
          <ViewField label="Language"/>
          <ViewField label="Created Date"/>
          <li>
            <hr/>
          </li>
          <ViewField label="ID" fixed/>
          <ViewField label="Hidden Status" fixed/>
          <ViewField label="Status Category" fixed/>
          <ViewField label="Type" fixed/>
          <ViewField label="Slug" fixed/>
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
          <ViewField label="Status"/>
          <ViewField label="Submitter"/>
          <ViewField label="Language"/>
          <ViewField label="Created Date"/>
          <li>
            <hr/>
          </li>
          <ViewField label="ID" fixed/>
          <ViewField label="Hidden Status" fixed/>
          <ViewField label="Status Category" fixed/>
          <ViewField label="Type" fixed/>
          <ViewField label="Slug" fixed/>
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

