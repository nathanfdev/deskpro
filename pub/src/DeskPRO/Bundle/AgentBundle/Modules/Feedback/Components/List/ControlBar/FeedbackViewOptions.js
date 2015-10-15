import React, {Component, PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import MenuFooterLink from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterLink';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';
import ItemGroup from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemGroup';

export class FeedbackViewOptions extends Component {

  static propTypes = {};

  render() {
    return (
      <Menu>
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
        <MenuFooter>
          <MenuFooterLink icon="cog">
            Thing
          </MenuFooterLink>
        </MenuFooter>
      </Menu>
    /** <Menu widgetClass="dpw-navigation-dropdown-secondary">
     <Item keepOpen>
     Hello!
     <ItemList>
     <Item>Test A</Item>
     <Item>Test B</Item>
     </ItemList>
     </Item>
     <Item>
     <span className="dpw-navigation-dropdown-item-mark">
     <span className="dpw-navigation-dropdown-item-disc"></span>
     </span>

     <span className="dpw-navigation-dropdown-item-title">List View</span>
     </Item>

     <Item>
     <span className="dpw-navigation-dropdown-item-mark">
     <span className="dpw-navigation-dropdown-item-disc dpw-navigation-dropdown-item-disc-active"></span>
     </span>
     <span className="dpw-navigation-dropdown-item-title">Date Created</span>
     <ItemList>
     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Status</span>
     </Item>

     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Submitter</span>
     </Item>

     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Language</span>
     </Item>

     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Created Date</span>
     </Item>

     <li>
     <hr/>
     </li>

     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">ID</span>
     </Item>


     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Hidden Status</span>
     </Item>

     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Status Category</span>
     </Item>

     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Type</span>
     </Item>

     <Item widgetClass="dpw-navigation-dropdown-column-list-item" overrideWidgetClass>
     <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
     <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
     <span className="dpw-navigation-dropdown-column-list-title">Slug</span>
     </Item>
     </ItemList>
     </Item>
     <Item>
     <span className="dpw-navigation-dropdown-item-mark">
     <span className="dpw-navigation-dropdown-item-disc"></span>
     </span>
     <span className="dpw-navigation-dropdown-item-title">Card View</span>
     </Item>

     <Item>
     <span className="dpw-navigation-dropdown-item-mark">
     <span className="dpw-navigation-dropdown-item-disc"></span>
     </span>
     <span className="dpw-navigation-dropdown-item-title">Calendar View</span>
     </Item>
     </Menu> */
    );
  }
}