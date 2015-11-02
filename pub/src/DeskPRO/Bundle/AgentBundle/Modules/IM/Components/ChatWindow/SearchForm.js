import React, { PropTypes } from 'react';

export class SearchForm extends React.Component {

  static propTypes = {
    handleType: PropTypes.func.isRequired,
    handleSearch: PropTypes.func.isRequired
  };

  render() {
    return (
      <div className="active-chat-search">
        <form onSubmit={this.props.handleSearch}>
          <input type="text" placeholder="Search chat history" id="active-chat-search-input" onChange={this.props.handleType}/>
          <a href="#" className="clear-input invisible" id="active-chat-search-clear">
            <i className="fa fa-times"></i>
          </a>
          <input type="submit" value="&#xf002;"/>
        </form>
      </div>
    );
  }
}
