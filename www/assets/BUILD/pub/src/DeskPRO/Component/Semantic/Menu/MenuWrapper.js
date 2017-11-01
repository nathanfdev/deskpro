import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import Menu from './Menu';
import SearchBox from '../SearchBox';

class MenuWrapper extends React.Component {
  static propTypes = {
    searchBox: PropTypes.bool,
    sections:  PropTypes.arrayOf(PropTypes.object),
    children:  PropTypes.node,
    className: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      filterText: ''
    };
  }

  getSearchBox() {
    const { searchBox } = this.props;
    const props = {
      text:        this.state.filterText,
      onUserInput: this.handleUserInput.bind(this)
    };
    if (searchBox) {
      return <SearchBox {...props} />;
    }
    return null;
  }

  getSections() {
    const items = [];
    const { sections } = this.props;
    let i = 0;
    if (!sections) {
      return [];
    }
    for (const section of sections) {
      section.key = String(i);
      i += 1;
      section.filterText = this.state.filterText;
      items.push(<Menu {...section} />);
    }
    return items;
  }

  handleUserInput(filterText) {
    this.setState({ filterText });
  }

  render() {
    const { children, className } = this.props;
    return (<div className={classNames('ui vertical menu', className)}>
      {this.getSearchBox()}
      {this.getSections()}
      {children}
    </div>);
  }
}
export default MenuWrapper;
