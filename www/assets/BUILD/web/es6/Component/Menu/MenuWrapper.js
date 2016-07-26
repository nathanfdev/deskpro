import React, { PropTypes } from 'react';
import { Menu } from 'Component/Menu/Menu';
import { SearchBox } from 'Component/SearchBox';

export class MenuWrapper extends React.Component {
  static propTypes = {
    structure: PropTypes.object.isRequired
  };

  render() {
    let sections = [];
    for (let section of this.props.structure.sections) {
      sections.push(<Menu section={section} />);
    }
    let searchBox = '';
    if (this.props.structure.searchBox) {
      searchBox = <SearchBox />
    }
    return <div className="ui vertical menu">
      {searchBox}
      {sections}
    </div>
  }
}