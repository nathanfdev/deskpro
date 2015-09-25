import React from 'react';

export default class Menu extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node,
    menuLevel: React.PropTypes.number,
    isOpen: React.PropTypes.bool,
    closeMenu: React.PropTypes.func,
  }

  /**
   * Constructor
   * @param  {Object} props The props
   * @return {void}
   */
  constructor(props) {
    super(props);

    this.state = {
      activeItem: false
    };
  }

  /**
   * Set the active item for the menu
   * @param {mixed} item The item
   * @return {void}
   */
  setActiveItem(item) {
    this.setState({
      activeItem: item
    });
  }

  closeMenu() {
    if (this.props.closeMenu) {
      this.props.closeMenu();
    }
  }

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const baseClass = 'dpw-navigation-dropdown';
    const isOpen = typeof this.props.isOpen !== 'undefined' ? this.props.isOpen : true;

    let divClass = (this.props.widgetClass ? baseClass + ' ' + this.props.widgetClass : baseClass);

    if (this.props.overrideWidgetClass) {
      divClass = this.props.widgetClass;
    }

    if (!window.TMP_COUNT) {
      window.TMP_COUNT = 0;
    }

    if (isOpen) {
      const menuLevel = this.props.menuLevel ? this.props.menuLevel : 1;
      return (<div className={divClass} style={{zIndex: 1000 + menuLevel}}>
          <ul>
            {React.Children.map(this.props.children, (child) => {
              window.TMP_COUNT++;
              const ref = window.TMP_COUNT;

              return React.cloneElement(child, { counter: ref, parentMenuLevel: menuLevel, activeItem: this.state.activeItem, setActiveItem: this.setActiveItem.bind(this), closeMenu: this.closeMenu.bind(this) });
            })}
          </ul>
        </div>);
    }

    return <div />;
  }
}
