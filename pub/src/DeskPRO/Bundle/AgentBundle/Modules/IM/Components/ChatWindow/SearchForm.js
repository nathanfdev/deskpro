import React from 'react';

export class SearchForm extends React.Component {
  render() {
    return (
      <div className="active-chat-search">
        <form>
          <input type="text" placeholder="Search chat history" id="active-chat-search-input" onChange={this.props.handleQuery}/>
          <a href="#" className="clear-input invisible" id="active-chat-search-clear">
            <i className="fa fa-times"></i>
          </a>
          <input type="submit" onClick={this.props.handleSearch} value="&#xf002;"/>
        </form>
      </div>
    );
  }
}
