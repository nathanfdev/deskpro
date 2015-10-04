import React from 'react';
import Message from './Message'

export class SearchForm extends React.Component {
  render() {
    return (
      <div className="active-chat-search">
        <form>
          <input type="text" placeholder="Search chat history" id="active-chat-search-input"/>
          <a href="#" className="clear-input invisible" id="active-chat-search-clear">
            <i className="fa fa-times"></i>
          </a>
          <input type="submit" value="&#xf002;"/>
        </form>
      </div>
    );
  }
}
