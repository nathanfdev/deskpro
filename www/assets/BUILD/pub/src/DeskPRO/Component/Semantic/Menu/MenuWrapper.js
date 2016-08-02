import React, { PropTypes } from 'react';
import Menu from './Menu';
import SearchBox from '../SearchBox';

class MenuWrapper extends React.Component {
  static propTypes = {
    searchBox: PropTypes.bool,
    sections:  PropTypes.arrayOf(PropTypes.object)
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
    for (const section of sections) {
      section.key = String(i++);
      section.filterText = this.state.filterText;
      items.push(<Menu {...section} />);
    }
    return items;
  }

  handleUserInput(filterText) {
    this.setState({ filterText });
  }

  render() {
    return (<div className="ui vertical menu">
      {this.getSearchBox()}
      {this.getSections()}
    </div>);
  }
}
export default MenuWrapper;
