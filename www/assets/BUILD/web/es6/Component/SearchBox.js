import React, { PropTypes } from 'react';

class SearchBox extends React.Component {
  static propTypes = {
    placeholder: PropTypes.string
  };
  
  render() {
    const {placeholder} = this.props;
    return <div className="item">
      <div className="ui input left icon search">
        <i className="search icon"/>
        <input type="search" placeholder={placeholder}/>
      </div>
    </div>
  }
}
export default SearchBox;