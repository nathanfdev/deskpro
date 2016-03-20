import React, { PropTypes } from 'react';

export class SearchResults extends React.Component {

  static propTypes = {
    tickets: PropTypes.object,
    articles: PropTypes.object,
    chats: PropTypes.object,

    onSelect: PropTypes.func
  };

  onClick(type, value) {
    this.props.onSelect && this.props.onSelect(type, value);
  }

  render() {
    const { tickets, articles, chats } = this.props;

    return (
      <div>
        {tickets.size && [
          <h4>Tickets</h4>,
          <ul>
            {tickets.valueSeq().map((ticket) =>
              <li key={ticket.get('id')} onClick={this.onClick.bind(this, 'ticket', ticket.get('id'))}>
                {ticket.get('subject')}
              </li>
            )}
          </ul>
        ]}

        {articles.size && [
          <h4>Articles</h4>,
          <ul>
            {articles.valueSeq>articles.valueSeq().map((article) =>
              <li key={article.get('id')} onClick={this.onClick.bind(this, 'article', article.get('id'))}>
                Article
              </li>
            )}
          </ul>
        ]}


        {chats.size && [
          <h4>Chats</h4>,
          <ul>
            {chats.valueSeq().map((chat) =>
              <li key={chat.get('id')} onClick={this.onClick.bind(this, 'chat', chat.get('id'))}>
                Chat
              </li>
            )}
          </ul>
        ]}
      </div>
    );
  }
}
