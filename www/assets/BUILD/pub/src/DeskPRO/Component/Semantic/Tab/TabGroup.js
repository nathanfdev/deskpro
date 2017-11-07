import PropTypes from 'prop-types';
import React from 'react';
import invariant from 'invariant';
import classNames from 'classnames';

// Use the following structure
// <TabGroup>
//   <Tab key="1" label="First">First tab content</Tab>
//   <Tab key="2" label="Second">Second tab content</Tab>
// </TabGroup>
// /!\ Don't forget key and label /!\

class TabGroup extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string,
    onChange:  PropTypes.func
  };
  static defaultProps = {
    onChange() {
    }
  };

  static getIcon(child) {
    const { icon } = child.props;
    if (icon) {
      return <i className={classNames('icon', icon)} />;
    }
    return null;
  }

  constructor(props) {
    super(props);
    this.state = {
      active: null
    };
  }

  componentWillMount() {
    const children = React.Children.toArray(this.props.children);
    invariant(children[0].key !== '.0', 'Tab components must have a key property');
    this.setState({
      active: children[0].key.replace(/\.\$/, '')
    });
  }

  setActive = (key) => {
    this.setState({
      active: key
    });
    this.props.onChange(key);
  };

  getMenu = () => React.Children.map(this.props.children,
    (child) => {
      invariant(child.props.label, 'Tab components must have a label property');
      invariant(child.key, 'Tab components must have a key property');
      return (<a
        key={'menu_child.key'}
        className={classNames('item', { active: child.key === this.state.active })}
        onClick={() => { this.setActive(child.key); }}
      >
        {TabGroup.getIcon(child)}
        {child.props.label}
      </a>);
    }
  );

  render() {
    const { className, children } = this.props;
    const childrenWithClasses = React.Children.map(children,
      child => React.cloneElement(child, {
        className: classNames({ active: child.key === this.state.active })
      })
    );
    return (
      <div>
        <div className={classNames('ui top attached tabular menu', className)}>
          {this.getMenu()}
        </div>
        {childrenWithClasses}
      </div>
    );
  }
}
export default TabGroup;
