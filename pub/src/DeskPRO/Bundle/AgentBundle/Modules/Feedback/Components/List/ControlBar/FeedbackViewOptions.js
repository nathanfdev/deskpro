import React, {Component, PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';

export class FeedbackViewOptions extends Component {

  static propTypes = {};

  render() {
    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
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
      </Menu>
    );
  }
}