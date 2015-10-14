import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

export default class Menu extends Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children: PropTypes.node,
    menuLevel: PropTypes.number,
    isOpen: PropTypes.bool,
    closeMenu: PropTypes.func
  };

  /**
   * Constructor
   * @param  {Object} props The props
   * @return {void}
   */
  constructor(props) {
    super(props);

    this.state = {
      activeItem: {}
    };
  }

  /**
   * Set the active item for the menu
   * @param {mixed} item The item
   * @return {void}
   */
  setActiveItem(item) {
    let activeItem = item;

    if (!item) {
      activeItem = {};
    }

    this.setState({
      activeItem: activeItem
    });
  }

  closeMenu() {
    if (this.props.closeMenu) {
      this.props.closeMenu();
    }
  }

  /**
   * Render the menu
   * @return {XML} The menu container
   */
  render() {
    const {widgetClass} = this.props;
    const isOpen = typeof this.props.isOpen !== 'undefined' ? this.props.isOpen : true;
    var divClass = classNames({
      'dpw-navigation-dropdown': !this.props.overrideWidgetClass
    });
    if (widgetClass) {
      divClass += ' ' + widgetClass;
    }

    if (!window.TMP_COUNT) {
      window.TMP_COUNT = 0;
    }

    if (isOpen) {
      const menuLevel = this.props.menuLevel ? this.props.menuLevel : 1;
      return (
        <div className={divClass} style={{zIndex: 1000 + menuLevel}}>
          <ul>
            {React.Children.map(this.props.children, (child, index) => {
              window.TMP_COUNT++;
              const ref = window.TMP_COUNT;

              return React.cloneElement(child,
                {
                  key: index,
                  counter: ref,
                  parentMenuLevel: menuLevel,
                  activeItem: this.state.activeItem,
                  setActiveItem: this.setActiveItem.bind(this),
                  closeMenu: this.closeMenu.bind(this)
                }
              );
            })}
          </ul>
        </div>
      );
    }

    return <div />;
  }
}
