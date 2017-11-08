import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import List from './List';

class ListElement extends React.PureComponent {
  static propTypes = {
    label:       PropTypes.string,
    description: PropTypes.string,
    icon:        PropTypes.string,
    image:       PropTypes.string,
    href:        PropTypes.string,
    imageNode:   PropTypes.object,
    elements:    PropTypes.arrayOf(PropTypes.object),
    className:   PropTypes.string,
    onClick:     PropTypes.func,
    children:    PropTypes.oneOfType([
      PropTypes.object,
      PropTypes.array
    ])
  };

  static defaultProps = {
    onClick() {

    },
    href:      '',
    label:     '',
    className: ''
  };

  getContent() {
    const { label, description, elements } = this.props;
    let content = [];
    if (description) {
      content = [
        <a className="header">{label}</a>,
        <div className="description">{description}</div>
      ];
    } else if (label) {
      content = [label];
    }
    if (elements) {
      const props = {
        elements
      };
      content.push(<List {...props} />);
    }
    return content.length ? <div className="content">{content}</div> : null;
  }

  getIcon() {
    const { icon, image, imageNode } = this.props;
    if (icon) {
      return <i className={classNames('icon', icon)} />;
    }
    if (image) {
      return <img className="ui avatar image" src={image} role="presentation" />;
    }

    if (imageNode) {
      return imageNode;
    }
    return null;
  }

  render() {
    const { className, children, href } = this.props;
    if (href) {
      return (<a href={href} className={classNames('item', className)} onClick={this.props.onClick}>
        {this.getIcon()}
        {this.getContent()}
        {children}
      </a>);
    }
    return (<div className={classNames('item', className)} onClick={this.props.onClick}>
      {this.getIcon()}
      {this.getContent()}
      {children}
    </div>);
  }
}
export default ListElement;
