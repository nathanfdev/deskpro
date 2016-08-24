import React, { PropTypes } from 'react';
import classNames from 'classnames';
import List from './List';

class ListElement extends React.Component {
  static propTypes = {
    label:       PropTypes.string,
    description: PropTypes.string,
    icon:        PropTypes.string,
    image:       PropTypes.string,
    elements:    PropTypes.arrayOf(PropTypes.object),
    classes:     PropTypes.arrayOf(PropTypes.string),
    children:    PropTypes.oneOfType([
      PropTypes.object,
      PropTypes.array
    ])
  };

  static defaultProps = {
    label:   '',
    classes: []
  };

  getContent() {
    const { label, description, elements } = this.props;
    let content = [];
    if (description) {
      content = [
        <a className="header">{label}</a>,
        <div className="description">{description}</div>
      ];
    } else {
      content = [label];
    }
    if (elements) {
      const props = {
        elements
      };
      content.push(<List {...props} />);
    }
    return content;
  }

  getIcon() {
    const { icon, image } = this.props;
    if (icon) {
      return <i className={classNames('icon', icon)} />;
    }
    if (image) {
      return <img className="ui avatar image" src={image} role="presentation" />;
    }
    return null;
  }

  render() {
    return (<div className={classNames('item', this.props.classes)}>
      {this.getIcon()}
      <div className="content">
        {this.getContent()}
        {this.props.children}
      </div>
    </div>);
  }
}
export default ListElement;
