import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class TopBarItem extends React.Component {
  static propTypes = {
    onClick:         PropTypes.func,
    children:        PropTypes.node,
    className:       PropTypes.string,
    childrenWrapper: PropTypes.string,
    title:           PropTypes.string
  };
  static defaultProps = {
    onClick() {}
  };

  handleClick = (e) => {
    this.props.onClick(e);
  };

  render() {
    const { children, className, childrenWrapper, title } = this.props;
    return (
      <div
        className={classNames('item', className)}
        onClick={this.handleClick}
        title={title}
      >
        <div className={childrenWrapper}>
          {children}
        </div>
      </div>
    );
  }
}
export default TopBarItem;
