import React, { PropTypes } from 'react';
import classNames from 'classnames';
import List from 'Components/List/List';

class ListElement extends React.Component {
  static propTypes = {
    label: PropTypes.string,
    description: PropTypes.string,
    icon: PropTypes.string,
    elements: PropTypes.arrayOf(PropTypes.object)
  };

  constructor(props) {
    super(props);
  }

  getContent() {
    const {label, description, elements} = this.props;
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
        elements: elements
      };
      content.push(<List {...props} />);
    }
    return content;
  }

  render() {
    const {icon} = this.props;
    return <div className="item">
      <i className={classNames('icon', icon)} />
      <div className="content">
        {this.getContent()}
      </div>
    </div>
  }
}
export default ListElement