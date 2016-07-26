import React, { PropTypes } from 'react';

export class SearchBox extends React.Component {
  static propTypes = {
    placeholder: PropTypes.string
  };
  
  render() {
    return <div className="item">
      <div className="ui input left icon search">
        <i className="search icon"/>
        <input type="search" placeholder={this.props.placeholder}/>
      </div>
    </div>
  }
}