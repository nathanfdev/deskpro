import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class ButtonGroup extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    activeKey: PropTypes.string,
    className: PropTypes.string,
    onChange:  PropTypes.func,
  };
  static defaultProps = {
    onChange() {
    }
  };

  constructor(props) {
    super(props);
    this.state = {
      activeKey: ''
    };
  }

  onClickButton(key, child) {
    let activeKey = this.state.activeKey;
    activeKey = activeKey === key ? '' : key;
    this.setActiveKey(activeKey);
    child.props.onClick();
  }

  setActiveKey = (activeKey) => {
    if (!('activeKey' in this.props)) {
      this.setState({
        activeKey
      });
    }
    this.props.onChange(activeKey);
  };


  render() {
    const { className } = this.props;
    let { children } = this.props;
    let i = 0;

    children = React.Children.map(children, (child) => {
      const key = child.key ? child.key : i;
      i += 1;
      const active = (key === this.state.activeKey) || (key === this.props.activeKey);
      const onClick = () => this.onClickButton(key, child);
      return React.cloneElement(child, {
        className: classNames(child.props.className, { active }),
        onClick
      });
    });

    return (
      <div
        className={classNames('ui buttons', className)}
      >
        {children}
      </div>

    );
  }
}
export default ButtonGroup;
