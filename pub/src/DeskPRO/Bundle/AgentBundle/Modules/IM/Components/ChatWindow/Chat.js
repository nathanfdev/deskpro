import React from 'react';
import Footer from './Footer';
import Header from './Header';
import MessageList from './MessageList';
import Offline from './Offline';
import SearchForm from './SearchForm';

export default class Chat extends React.Component {
     render () {
        return (
            <div className="dropdown active-chat-dropdown" id="active-chat-dropdown">
                <Header target={this.props.target} handleCloseChat={this.props.handleCloseChat}/>

                <SearchForm />

                <div className="chat-controls"><a href="#">Load old messages</a></div>

                <MessageList messages={this.props.messages} />

                <div className="active-chat-user-typing">Jeniffer is typing a message <span id="typing">...</span></div>

                <Offline />

                <Footer />
            </div>
        );
    }
}
