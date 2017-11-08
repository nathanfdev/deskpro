import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import TabsMenu from './TabsMenu';
import TabsItem from './TabsItem';
import TabsMenuItem from './TabsMenuItem';


class Tabs extends React.Component {

  static propTypes = {
    items: PropTypes.arrayOf(PropTypes.shape({
      id:        PropTypes.string,
      title:     PropTypes.oneOfType([PropTypes.string, PropTypes.node]),
      className: PropTypes.string,
      content:   PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.node
      ]).isRequired
    })),
    classes: PropTypes.shape({
      menuItem:          PropTypes.arrayOf(PropTypes.string),
      notActiveMenuItem: PropTypes.arrayOf(PropTypes.string),
      activeMenuItem:    PropTypes.arrayOf(PropTypes.string),
      item:              PropTypes.arrayOf(PropTypes.string)
    })
  };

  constructor(props) {
    super(props);
    this.state = {
      activeId: null
    };

    this.onTabClick = this.onTabClick.bind(this);
  }

  onTabClick(tabId) {
    return () => {
      this.setState({ activeId: tabId });
    };
  }

  getMenuItems() {
    const { items, classes } = this.props;
    const menuItems = [];
    const self = this;
    let key = 1;
    items.map((item, index) => {
      const active = self.state.activeId ? self.state.activeId === item.id : index === 0;
      let allClasses = classes.menuItem ? classes.menuItem : [];
      if (active) {
        allClasses = allClasses.concat(classes.activeMenuItem);
      } else {
        allClasses = allClasses.concat(classes.notActiveMenuItem);
      }
      const props = {
        key,
        active,
        tabId:     item.id,
        title:     item.title,
        className: classNames(allClasses),
        onClick:   self.onTabClick(item.id)
      };
      key += 1;
      return menuItems.push(<TabsMenuItem {...props} />);
    });

    return menuItems;
  }

  getItems() {
    const { items, classes } = this.props;
    const tabsItems = [];
    const self = this;
    let key = 1;
    items
      .filter((item, index) => {
        if (self.state.activeId) {
          return self.state.activeId === item.id;
        }
        return index === 0;
      })
      .map((item, index) => {
        const props = {
          key,
          tabId:     item.id,
          active:    self.state.activeId ? self.state.activeId === item.id : index === 0,
          content:   item.content,
          className: classNames(classes.item, item.className ? item.className : '')
        };
        key += 1;
        return tabsItems.push(<TabsItem {...props} />);
      });

    return tabsItems;
  }

  render() {
    return (
      <div>
        <TabsMenu>
          {this.getMenuItems()}
        </TabsMenu>
        {this.getItems()}
      </div>
    );
  }
}
export default Tabs;
