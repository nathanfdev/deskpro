import React, { PropTypes } from 'react';
import TabsMenu from './TabsMenu';
import TabsItem from './TabsItem';
import TabsMenuItem from './TabsMenuItem';

class Tabs extends React.Component {

  static propTypes = {
    items: PropTypes.arrayOf(PropTypes.shape({
      id:      PropTypes.string,
      title:   PropTypes.string,
      content: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.node
      ]).isRequired
    })),
    classes: PropTypes.shape({
      menuItem: PropTypes.arrayOf(PropTypes.string),
      item:     PropTypes.arrayOf(PropTypes.string)
    })
  };

  static defaultProps = {
    classes: {
      menuItem: [],
      item:     []
    }
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
    items.map((item, index) => {
      const props = {
        tabId:   item.id,
        active:  self.state.activeId ? self.state.activeId === item.id : index === 0,
        title:   item.title,
        classes: classes.menuItem,
        onClick: self.onTabClick(item.id)
      };
      return menuItems.push(<TabsMenuItem {...props} />);
    });

    return menuItems;
  }

  getItems() {
    const { items, classes } = this.props;
    const tabsItems = [];
    const self = this;

    items.map((item, index) => {
      const props = {
        tabId:   item.id,
        active:  self.state.activeId ? self.state.activeId === item.id : index === 0,
        content: item.content,
        classes: classes.item
      };
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
