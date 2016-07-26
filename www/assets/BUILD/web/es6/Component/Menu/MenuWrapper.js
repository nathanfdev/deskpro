import React, { PropTypes } from 'react';
import Menu from 'Component/Menu/Menu';
import SearchBox from 'Component/SearchBox';

class MenuWrapper extends React.Component {
  static propTypes = {
    searchBox: PropTypes.bool,
    sections: PropTypes.arrayOf(PropTypes.object)
  };

  getSearchBox() {
    const {searchBox} = this.props;
    if (searchBox) {
      return <SearchBox />
    }
  }

  getSections() {
    let items = [];
    const {sections} = this.props;
    for (const i in sections) {
      if (!sections.hasOwnProperty(i)) {
        continue;
      }
      let section = sections[i];
      section['key'] = String(i);
      items.push(<Menu {...section} />);
    }
    return items;
  }

  render() {
    return <div className="ui vertical menu">
      {this.getSearchBox()}
      {this.getSections()}
    </div>
  }
}
export default MenuWrapper;